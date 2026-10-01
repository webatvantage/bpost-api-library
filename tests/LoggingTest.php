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
use Webatvantage\Bpost\Api\Tests\Doubles\SpyLogHandler;

class LoggingTest extends TestCase
{
	private SpyLogger $logger;

	private SpyLogHandler $logHandler;

	protected function setUp(): void
	{
		parent::setUp();

		$this->logger = new SpyLogger();
		$this->logHandler = new SpyLogHandler();
	}

	public function test_a_logger_is_written_to_from_the_first_call()
	{
		$adapter = $this->loggingAdapter();

		$this->mockResponse(200, '<orderInfo/>');
		$adapter->request(new FakeRequest(Method::GET, '/orders/ref-1'));

		$this->assertNotEmpty($this->logger->records);
	}

	public function test_a_client_without_a_logger_writes_nothing()
	{
		$adapter = new HttpApiAdapter(
			baseUri: 'https://example.test',
			httpClientOptions: ['handler' => $this->handlerStack()],
		);

		$this->mockResponse(200, '<orderInfo/>');
		$adapter->request(new FakeRequest(Method::GET, '/orders/ref-1'));

		$this->assertEmpty($this->logger->records);
	}

	/**
	 * One set of client options is shared by every service, so the log middleware used to be
	 * pushed onto the caller's own handler stack once per service — three services meant every
	 * request was written to the log two and a bit times over.
	 */
	public function test_reaching_more_services_does_not_log_a_call_more_than_once()
	{
		$order = '<orderInfo><reference>ref-1</reference></orderInfo>';

		$client = $this->client();
		$this->mockResponse(200, $order);
		$client->shm()->orders()->get('ref-1');

		$withOneService = count($this->logHandler->choices);
		$this->assertNotSame(0, $withOneService);

		$client->geo();
		$client->parcel();

		$this->logHandler->choices = [];
		$this->mockResponse(200, $order);
		$client->shm()->orders()->get('ref-1');

		$this->assertCount($withOneService, $this->logHandler->choices);
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
		$adapter = $this->loggingAdapter();

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
	 * A transport failure never reaches a status, and losing a name that will not resolve is the
	 * opposite of useful.
	 */
	public function test_a_transport_failure_is_logged()
	{
		$mock = new MockHandler([
			new ConnectException('Could not resolve host', new PsrRequest('GET', '/orders')),
		]);

		$adapter = new HttpApiAdapter(
			baseUri: 'https://example.test',
			httpClientOptions: ['handler' => HandlerStack::create($mock)],
			logger: $this->logger,
		);

		try
		{
			$adapter->request(new FakeRequest(Method::GET, '/orders/ref'));
		}
		catch (BpostException)
		{
		}

		$this->assertNotEmpty($this->logger->records);
	}

	public function test_a_call_carries_what_the_client_was_told()
	{
		$adapter = $this->spyingAdapter();

		$this->mockResponse(200, '<orderInfo/>');
		$adapter->request(new FakeRequest(Method::GET, '/orders/ref-1'));

		$this->assertFalse($this->logHandler->lastChoice());

		$adapter->withLogging();

		$this->mockResponse(200, '<orderInfo/>');
		$adapter->request(new FakeRequest(Method::GET, '/orders/ref-2'));

		$this->assertTrue($this->logHandler->lastChoice());
	}

	public function test_a_request_overrules_the_client_either_way()
	{
		$adapter = $this->spyingAdapter()->withLogging();

		$this->mockResponse(200, '<orderInfo/>');
		$adapter->request(new FakeRequest(Method::GET, '/orders/ref-1')->withoutLogging());

		$this->assertFalse($this->logHandler->lastChoice());

		$adapter->withLogging(false);

		$this->mockResponse(200, '<orderInfo/>');
		$adapter->request(new FakeRequest(Method::GET, '/orders/ref-2')->withLogging());

		$this->assertTrue($this->logHandler->lastChoice());
	}

	public function test_a_resource_reaches_only_the_calls_made_on_it()
	{
		$client = $this->client();

		$this->mockResponse(200, '<orderInfo><reference>ref-1</reference></orderInfo>');
		$client->shm()->orders()->withLogging()->get('ref-1');

		$this->assertTrue($this->logHandler->lastChoice());

		$this->mockResponse(200, '<orderInfo><reference>ref-2</reference></orderInfo>');
		$client->shm()->orders()->get('ref-2');

		$this->assertFalse($this->logHandler->lastChoice());
	}

	public function test_switching_the_whole_client_on_reaches_the_services_built_before_and_after()
	{
		$client = $this->client();
		$shm = $client->shm();

		$client->withLogging();

		$this->mockResponse(200, '<orderInfo><reference>ref-1</reference></orderInfo>');
		$shm->orders()->get('ref-1');

		$this->assertTrue($this->logHandler->lastChoice());

		$this->mockResponse(200, '<Poi/>');
		$client->geo()->servicePoints()->nearest(zone: '1000')->get();

		$this->assertTrue($this->logHandler->lastChoice());
	}

	private function loggingAdapter(): HttpApiAdapter
	{
		return new HttpApiAdapter(
			baseUri: 'https://example.test',
			httpClientOptions: ['handler' => $this->handlerStack()],
			logger: $this->logger,
		);
	}

	private function spyingAdapter(): HttpApiAdapter
	{
		return new HttpApiAdapter(
			baseUri: 'https://example.test',
			httpClientOptions: ['handler' => $this->handlerStack()],
			logger: $this->logger,
			logHandler: $this->logHandler,
		);
	}

	private function client(): BpostApiClient
	{
		return new BpostApiClient(
			new BpostApiConfig(
				shm: new ShmApiConfig(accountId: '123456', passphrase: 'passphrase'),
				geo: new GeoApiConfig(partner: '999999', apiKey: 'key'),
				parcel: new ParcelApiConfig(accountId: '123456', password: 'password'),
				logHandler: $this->logHandler,
			),
			['handler' => $this->handlerStack()],
			$this->logger,
		);
	}
}
