<?php

namespace Webatvantage\Bpost\Api\Tests;

use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request as PsrRequest;
use PHPUnit\Framework\Attributes\DataProvider;
use Webatvantage\Bpost\Api\ApiAdapter\HttpApiAdapter;
use Webatvantage\Bpost\Api\BpostApiClient;
use Webatvantage\Bpost\Api\BpostApiConfig;
use Webatvantage\Bpost\Api\Enums\Method;
use Webatvantage\Bpost\Api\Exceptions\BpostException;
use Webatvantage\Bpost\Api\Geo\GeoApiConfig;
use Webatvantage\Bpost\Api\Parcel\ParcelApiConfig;
use Webatvantage\Bpost\Api\Shm\ShmApiConfig;
use Webatvantage\Bpost\Api\Tests\Doubles\FakeRequest;
use Webatvantage\Bpost\Api\Tests\Doubles\SpyLogger;

class LoggingTest extends TestCase
{
	private SpyLogger $logger;

	protected function setUp(): void
	{
		parent::setUp();

		$this->logger = new SpyLogger();
	}

	public function test_a_logger_stays_quiet_until_it_is_switched_on()
	{
		$adapter = $this->loggingAdapter();

		$this->mockResponse(200, '<orderInfo/>');
		$adapter->request(new FakeRequest(Method::GET, '/orders/ref-1'));

		$this->assertEmpty($this->logger->records);

		$adapter->withLogging();

		$this->mockResponse(200, '<orderInfo/>');
		$adapter->request(new FakeRequest(Method::GET, '/orders/ref-2'));

		$this->assertNotEmpty($this->logger->records);
	}

	public function test_a_request_overrules_the_client_either_way()
	{
		$adapter = $this->loggingAdapter()->withLogging();

		$this->mockResponse(200, '<orderInfo/>');
		$adapter->request(new FakeRequest(Method::GET, '/orders/ref-1')->withoutLogging());

		$this->assertEmpty($this->logger->records);

		$adapter->withLogging(false);

		$this->mockResponse(200, '<orderInfo/>');
		$adapter->request(new FakeRequest(Method::GET, '/orders/ref-2')->withLogging());

		$this->assertNotEmpty($this->logger->records);
	}

	public function test_a_resource_logs_only_the_calls_made_on_it()
	{
		$client = $this->client();

		$this->mockResponse(200, '<orderInfo><reference>ref-1</reference></orderInfo>');
		$client->shm()->orders()->withLogging()->get('ref-1');

		$this->assertNotEmpty($this->logger->records);

		$this->logger->records = [];

		$this->mockResponse(200, '<orderInfo><reference>ref-2</reference></orderInfo>');
		$client->shm()->orders()->get('ref-2');

		$this->assertEmpty($this->logger->records);
	}

	public function test_switching_the_whole_client_on_reaches_the_services_built_before_and_after()
	{
		$client = $this->client();
		$shm = $client->shm();

		$client->withLogging();

		$this->mockResponse(200, '<orderInfo><reference>ref-1</reference></orderInfo>');
		$shm->orders()->get('ref-1');

		$this->assertNotEmpty($this->logger->records);

		$this->logger->records = [];

		$this->mockResponse(200, '<Poi/>');
		$client->geo()->servicePoints()->nearest(zone: '1000')->get();

		$this->assertNotEmpty($this->logger->records);
	}

	/**
	 * One set of client options is shared by every service, so the log middleware used to be
	 * pushed onto the caller's own handler stack once per service — three services meant every
	 * request was written to the log two and a bit times over.
	 */
	public function test_reaching_more_services_does_not_log_a_call_more_than_once()
	{
		$order = '<orderInfo><reference>ref-1</reference></orderInfo>';

		$client = $this->client()->withLogging();
		$this->mockResponse(200, $order);
		$client->shm()->orders()->get('ref-1');

		$withOneService = count($this->logger->records);
		$this->assertNotSame(0, $withOneService);

		$client->geo();
		$client->parcel();

		$this->logger->records = [];
		$this->mockResponse(200, $order);
		$client->shm()->orders()->get('ref-1');

		$this->assertCount($withOneService, $this->logger->records);
	}

	/**
	 * @return array<string, array{int, string}>
	 */
	public static function statusLevels(): array
	{
		return [
			'a success' => [200, 'info'],
			'a bpost fault' => [409, 'error'],
			'a bpost outage' => [500, 'critical'],
		];
	}

	/**
	 * The level carries the severity so a consumer's own logger threshold can keep the failures
	 * and drop the rest; without one every record went out at debug and read the same.
	 */
	#[DataProvider('statusLevels')]
	public function test_a_response_is_logged_at_the_level_its_status_deserves(int $status, string $level)
	{
		$adapter = $this->loggingAdapter()->withLogging();

		$this->mockResponse($status, '<orderInfo/>');

		try
		{
			$adapter->request(new FakeRequest(Method::GET, '/orders/ref'));
		}
		catch (BpostException)
		{
		}

		$this->assertSame($level, $this->logger->levelOf('Guzzle HTTP response'));
	}

	/**
	 * The request and the statistics stay below the failures, so a logger set to warning keeps
	 * only what went wrong.
	 */
	public function test_the_request_and_statistics_stay_at_debug()
	{
		$adapter = $this->loggingAdapter();

		$this->mockResponse(500, '<systemException/>');

		try
		{
			$adapter->request(new FakeRequest(Method::GET, '/orders/ref'));
		}
		catch (BpostException)
		{
		}

		$this->assertSame('debug', $this->logger->levelOf('Guzzle HTTP request'));
		$this->assertSame('debug', $this->logger->levelOf('Guzzle HTTP statistics'));
	}

	/**
	 * @return array<string, array{int, bool}>
	 */
	public static function silencedStatuses(): array
	{
		return [
			'a success is dropped' => [200, false],
			'a redirect is dropped' => [302, false],
			'a bpost fault is kept' => [409, true],
			'a bpost outage is kept' => [500, true],
		];
	}

	/**
	 * Switching the log off quiets the calls that worked, not the ones that did not: a refusal is
	 * the record worth having, and a caller who silenced a noisy call did not ask to lose it.
	 */
	#[DataProvider('silencedStatuses')]
	public function test_a_silenced_call_still_reports_what_bpost_refused(int $status, bool $logged)
	{
		$adapter = $this->loggingAdapter()->withoutLogging();

		$this->mockResponse($status, '<x/>');

		try
		{
			$adapter->request(new FakeRequest(Method::GET, '/orders/ref'));
		}
		catch (BpostException)
		{
		}

		$logged
			? $this->assertNotEmpty($this->logger->records)
			: $this->assertEmpty($this->logger->records);
	}

	/**
	 * A transport failure never reaches a status, and counts with the refusals rather than against
	 * them — losing a name that will not resolve is the opposite of useful.
	 */
	public function test_a_silenced_call_still_reports_a_transport_failure()
	{
		$mock = new MockHandler([
			new ConnectException('Could not resolve host', new PsrRequest('GET', '/orders')),
		]);

		$adapter = new HttpApiAdapter(
			baseUri: 'https://example.test',
			httpClientOptions: ['handler' => HandlerStack::create($mock)],
			logger: $this->logger,
		)->withoutLogging();

		try
		{
			$adapter->request(new FakeRequest(Method::GET, '/orders/ref'));
		}
		catch (BpostException)
		{
		}

		$this->assertNotEmpty($this->logger->records);
	}

	public function test_a_refusal_is_logged_at_its_own_level_even_when_silenced()
	{
		$adapter = $this->loggingAdapter()->withoutLogging();

		$this->mockResponse(500, '<systemException/>');

		try
		{
			$adapter->request(new FakeRequest(Method::GET, '/orders/ref'));
		}
		catch (BpostException)
		{
		}

		$this->assertSame('critical', $this->logger->levelOf('Guzzle HTTP response'));
	}

	private function loggingAdapter(): HttpApiAdapter
	{
		return new HttpApiAdapter(
			baseUri: 'https://example.test',
			httpClientOptions: ['handler' => $this->handlerStack()],
			logger: $this->logger,
		);
	}

	private function client(): BpostApiClient
	{
		return new BpostApiClient(
			new BpostApiConfig(
				shm: new ShmApiConfig(accountId: '123456', passphrase: 'passphrase'),
				geo: new GeoApiConfig(partner: '999999', apiKey: 'key'),
				parcel: new ParcelApiConfig(accountId: '123456', password: 'password'),
			),
			['handler' => $this->handlerStack()],
			$this->logger,
		);
	}
}
