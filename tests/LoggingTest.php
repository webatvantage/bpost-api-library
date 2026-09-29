<?php

namespace Webatvantage\Bpost\Api\Tests;

use Webatvantage\Bpost\Api\ApiAdapter\HttpApiAdapter;
use Webatvantage\Bpost\Api\BpostApiClient;
use Webatvantage\Bpost\Api\BpostApiConfig;
use Webatvantage\Bpost\Api\Enums\Method;
use Webatvantage\Bpost\Api\Geo\GeoApiConfig;
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

	public function test_a_logger_logs_until_it_is_switched_off()
	{
		$adapter = $this->loggingAdapter();

		$this->mockResponse(200, '<orderInfo/>');
		$adapter->request(new FakeRequest(Method::GET, '/orders/ref-1'));

		$this->assertNotEmpty($this->logger->records);

		$this->logger->records = [];
		$adapter->setLogging(false);

		$this->mockResponse(200, '<orderInfo/>');
		$adapter->request(new FakeRequest(Method::GET, '/orders/ref-2'));

		$this->assertEmpty($this->logger->records);
	}

	public function test_a_request_overrules_the_client_either_way()
	{
		$adapter = $this->loggingAdapter();

		$this->mockResponse(200, '<orderInfo/>');
		$adapter->request(new FakeRequest(Method::GET, '/orders/ref-1')->withoutLogging());

		$this->assertEmpty($this->logger->records);

		$adapter->setLogging(false);

		$this->mockResponse(200, '<orderInfo/>');
		$adapter->request(new FakeRequest(Method::GET, '/orders/ref-2')->withLogging());

		$this->assertNotEmpty($this->logger->records);
	}

	public function test_a_resource_silences_only_the_calls_made_on_it()
	{
		$client = $this->client();

		$this->mockResponse(200, '<orderInfo><reference>ref-1</reference></orderInfo>');
		$client->shm()->orders()->withoutLogging()->get('ref-1');

		$this->assertEmpty($this->logger->records);

		$this->mockResponse(200, '<orderInfo><reference>ref-2</reference></orderInfo>');
		$client->shm()->orders()->get('ref-2');

		$this->assertNotEmpty($this->logger->records);
	}

	public function test_switching_the_whole_client_off_reaches_the_services_built_before_and_after()
	{
		$client = $this->client();
		$shm = $client->shm();

		$client->withoutLogging();

		$this->mockResponse(200, '<orderInfo><reference>ref-1</reference></orderInfo>');
		$shm->orders()->get('ref-1');

		$this->mockResponse(200, '<Poi/>');
		$client->geo()->servicePoints()->nearest(zone: '1000')->get();

		$this->assertEmpty($this->logger->records);
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
			),
			['handler' => $this->handlerStack()],
			$this->logger,
		);
	}
}
