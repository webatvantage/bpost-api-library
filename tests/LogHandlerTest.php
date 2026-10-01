<?php

namespace Webatvantage\Bpost\Api\Tests;

use GuzzleHttp\TransferStats;
use GuzzleLogMiddleware\Handler\HandlerInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use Throwable;
use Webatvantage\Bpost\Api\ApiAdapter\HttpApiAdapter;
use Webatvantage\Bpost\Api\BpostApiClient;
use Webatvantage\Bpost\Api\BpostApiConfig;
use Webatvantage\Bpost\Api\Enums\Method;
use Webatvantage\Bpost\Api\Shm\ShmApiConfig;
use Webatvantage\Bpost\Api\Tests\Doubles\FakeRequest;
use Webatvantage\Bpost\Api\Tests\Doubles\SpyLogger;

class LogHandlerTest extends TestCase
{
	private SpyLogger $logger;

	protected function setUp(): void
	{
		parent::setUp();

		$this->logger = new SpyLogger();
	}

	/**
	 * One record per call, so a count tells the handler was reached and the default was not.
	 */
	private function countingHandler(): HandlerInterface
	{
		return new class () implements HandlerInterface {
			public function log(
				LoggerInterface $logger,
				RequestInterface $request,
				?ResponseInterface $response = null,
				?Throwable $exception = null,
				?TransferStats $stats = null,
				array $options = [],
			): void {
				$logger->alert('the handler the caller brought');
			}
		};
	}

	public function test_a_supplied_handler_replaces_the_default_one()
	{
		$adapter = new HttpApiAdapter(
			baseUri: 'https://example.test',
			httpClientOptions: ['handler' => $this->handlerStack()],
			logger: $this->logger,
			logHandler: $this->countingHandler(),
		);

		$this->mockResponse(200, '<orderInfo/>');
		$adapter->request(new FakeRequest(Method::GET, '/orders/ref'));

		$this->assertSame('alert', $this->logger->levelOf('the handler the caller brought'));
		$this->assertNull($this->logger->levelOf('Guzzle HTTP response'));
	}

	/**
	 * The knob sits on the shared config because that is the one a caller holds when they reach a
	 * service through the central client rather than constructing it themselves.
	 */
	public function test_the_config_carries_the_handler_to_a_service()
	{
		$client = new BpostApiClient(
			new BpostApiConfig(
				shm: new ShmApiConfig(accountId: '123456', passphrase: 'passphrase'),
				logHandler: $this->countingHandler(),
			),
			['handler' => $this->handlerStack()],
			$this->logger,
		);

		$this->mockResponse(200, '<orderInfo><reference>ref-1</reference></orderInfo>');
		$client->shm()->orders()->get('ref-1');

		$this->assertSame('alert', $this->logger->levelOf('the handler the caller brought'));
	}

	public function test_the_default_handler_stands_when_the_config_names_none()
	{
		$client = new BpostApiClient(
			new BpostApiConfig(shm: new ShmApiConfig(accountId: '123456', passphrase: 'passphrase')),
			['handler' => $this->handlerStack()],
			$this->logger,
		);

		$this->mockResponse(200, '<orderInfo><reference>ref-1</reference></orderInfo>');
		$client->shm()->orders()->get('ref-1');

		$this->assertSame('info', $this->logger->levelOf('Guzzle HTTP response'));
	}
}
