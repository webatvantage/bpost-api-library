<?php

namespace Webatvantage\Bpost\Api\Shm;

use Psr\Log\LoggerInterface;
use Webatvantage\Bpost\Api\ApiAdapter\HttpApiAdapter;
use Webatvantage\Bpost\Api\Shm\Resources\LabelResource;
use Webatvantage\Bpost\Api\Shm\Resources\OrderResource;
use Webatvantage\Bpost\Api\Shm\Resources\ProductConfigurationResource;

/**
 * The bpost Shipping Manager: orders, labels and what this account may sell.
 */
readonly class ShmApiClient
{
	private HttpApiAdapter $apiAdapter;

	/**
	 * @param array<string, mixed> $httpClientOptions
	 */
	public function __construct(
		private ShmApiConfig $config,
		array $httpClientOptions = [],
		?LoggerInterface $logger = null,
	) {
		$this->apiAdapter = new HttpApiAdapter(
			baseUri: $config->baseUri,
			defaultHeaders: ['Authorization' => $config->authorizationHeader()],
			httpClientOptions: $httpClientOptions,
			logger: $logger,
		);
	}

	/**
	 * Log every call this client makes, or keep them all out of the log.
	 */
	public function withLogging(bool $logging = true): static
	{
		$this->apiAdapter->setLogging($logging);

		return $this;
	}

	public function withoutLogging(): static
	{
		return $this->withLogging(false);
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
