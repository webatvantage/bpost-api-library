<?php

namespace Webatvantage\Bpost\Api\Parcel;

use Psr\Log\LoggerInterface;
use Webatvantage\Bpost\Api\ApiAdapter\HttpApiAdapter;
use Webatvantage\Bpost\Api\Parcel\Resources\AnnouncementResource;
use Webatvantage\Bpost\Api\Parcel\Resources\TrackingResource;

/**
 * bpost's trackedmail services: announcing a parcel, and following it afterwards.
 */
readonly class ParcelApiClient
{
	private HttpApiAdapter $apiAdapter;

	/**
	 * @param array<string, mixed> $httpClientOptions
	 */
	public function __construct(
		private ParcelApiConfig $config,
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

	public function announcements(): AnnouncementResource
	{
		return new AnnouncementResource($this->apiAdapter, $this->config);
	}

	public function tracking(): TrackingResource
	{
		return new TrackingResource($this->apiAdapter);
	}
}
