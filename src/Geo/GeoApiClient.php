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
			$config->baseUri,
			[
				// Mandatory on the pudo.bpost.cloud domain since manual section B.4.1.0.
				'x-api-key' => $config->apiKey,
				// Without this bpost truncates a Get All Service Points response over 10 MB.
				'Accept-Encoding' => 'gzip',
			],
			$httpClientOptions,
			$logger,
		);
	}

	public function servicePoints(): ServicePointResource
	{
		return new ServicePointResource($this->apiAdapter, $this->config);
	}
}
