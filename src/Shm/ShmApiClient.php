<?php

namespace Webatvantage\Bpost\Api\Shm;

use Closure;
use GuzzleLogMiddleware\Handler\HandlerInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use Webatvantage\Bpost\Api\ApiAdapter\HttpApiAdapter;
use Webatvantage\Bpost\Api\Contracts\Debuggable;
use Webatvantage\Bpost\Api\Contracts\Loggable;
use Webatvantage\Bpost\Api\Shm\Resources\LabelResource;
use Webatvantage\Bpost\Api\Shm\Resources\OrderResource;
use Webatvantage\Bpost\Api\Shm\Resources\ProductConfigurationResource;

/**
 * The bpost Shipping Manager: orders, labels and what this account may sell.
 */
readonly class ShmApiClient implements Debuggable, Loggable
{
	private HttpApiAdapter $apiAdapter;

	/**
	 * @param array<string, mixed> $httpClientOptions
	 */
	public function __construct(
		private ShmApiConfig $config,
		array $httpClientOptions = [],
		?LoggerInterface $logger = null,
		?HandlerInterface $logHandler = null,
	) {
		$this->apiAdapter = new HttpApiAdapter(
			baseUri: $config->baseUri,
			defaultHeaders: ['Authorization' => $config->authorizationHeader()],
			httpClientOptions: $httpClientOptions,
			logger: $logger,
			logHandler: $logHandler,
		);
	}

	/**
	 * Log every call this client makes, or keep them all out of the log.
	 */
	public function withLogging(bool $logging = true): static
	{
		$this->apiAdapter->withLogging($logging);

		return $this;
	}

	public function withoutLogging(): static
	{
		return $this->withLogging(false);
	}

	/**
	 * Hand every request and response this client sends to a callback.
	 *
	 * @param (Closure(RequestInterface $request, ResponseInterface $response): void)|null $callback
	 *
	 * @return static
	 */
	public function withDebug(?Closure $callback): static
	{
		$this->apiAdapter->withDebug($callback);

		return $this;
	}

	public function orders(): OrderResource
	{
		return new OrderResource($this->apiAdapter, $this->config);
	}

	public function labels(): LabelResource
	{
		return new LabelResource($this->apiAdapter, $this->config);
	}

	public function productConfiguration(): ProductConfigurationResource
	{
		return new ProductConfigurationResource($this->apiAdapter, $this->config);
	}
}
