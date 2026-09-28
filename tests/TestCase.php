<?php

namespace Webatvantage\Bpost\Api\Tests;

use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request as PsrRequest;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase as BaseTestCase;
use Webatvantage\Bpost\Api\ApiAdapter\HttpApiAdapter;

class TestCase extends BaseTestCase
{
	protected MockHandler $mock;

	/** @var array<PsrRequest> */
	protected array $recorded = [];

	protected function setUp(): void
	{
		parent::setUp();

		$this->mock = new MockHandler();
		$this->recorded = [];
	}

	/**
	 * A handler stack backed by the mock queue that also records what was sent.
	 */
	protected function handlerStack(): HandlerStack
	{
		$stack = HandlerStack::create($this->mock);
		$stack->push(function (callable $handler) {
			return function ($request, array $options) use ($handler) {
				$this->recorded[] = $request;

				return $handler($request, $options);
			};
		});

		return $stack;
	}

	/**
	 * @param array<string, string> $defaultHeaders
	 */
	protected function adapter(array $defaultHeaders = []): HttpApiAdapter
	{
		return new HttpApiAdapter('https://example.test', $defaultHeaders, ['handler' => $this->handlerStack()]);
	}

	/**
	 * @param array<string, string> $headers
	 */
	protected function mockResponse(int $status, string $body, array $headers = []): void
	{
		$this->mock->append(new Response($status, $headers, $body));
	}

	protected function lastRequest(): PsrRequest
	{
		return $this->recorded[count($this->recorded) - 1];
	}
}
