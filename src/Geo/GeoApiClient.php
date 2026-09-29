<?php

namespace Webatvantage\Bpost\Api\Geo;

use Psr\Log\LoggerInterface;
use Webatvantage\Bpost\Api\ApiAdapter\HttpApiAdapter;
use Webatvantage\Bpost\Api\Geo\Resources\ServicePointResource;

/**
 * The bpost Geolocator: pick-up points, parcel points and parcel lockers.
 */
readonly class GeoApiClient
{
	private HttpApiAdapter $apiAdapter;

	/**
	 * @param array<string, mixed> $httpClientOptions
	 */
	public function __construct(
		private GeoApiConfig $config,
		array $httpClientOptions = [],
		?LoggerInterface $logger = null,
	) {
		$this->apiAdapter = new HttpApiAdapter(
			baseUri: $config->baseUri,
			defaultHeaders: [
				// Mandatory on the pudo.bpost.cloud domain since manual section B.4.1.0.
				'x-api-key' => $config->apiKey,
				// Without this bpost truncates a Get All Service Points response over 10 MB.
				'Accept-Encoding' => 'gzip',
			],
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

	public function servicePoints(): ServicePointResource
	{
		return new ServicePointResource($this->apiAdapter, $this->config);
	}
}
