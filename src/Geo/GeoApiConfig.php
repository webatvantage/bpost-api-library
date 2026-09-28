<?php

namespace Webatvantage\Bpost\Api\Geo;

readonly class GeoApiConfig
{
	/**
	 * @param string $partner Your bpost account id, activated for the Geolocator. bpost sends it as
	 *                        `Partner` on most operations and as `Account` on Get All Service
	 *                        Points, but it is the same identifier
	 * @param string $apiKey The `x-api-key` bpost issues per account. Mandatory on every request to
	 *                       the pudo.bpost.cloud domain since manual section B.4.1.0
	 * @param string|null $appId Optional four-character application id, used by bpost for statistics
	 * @param string $baseUri
	 */
	public function __construct(
		public string $partner,
		public string $apiKey,
		public ?string $appId = null,
		public string $baseUri = 'https://pudo.bpost.cloud',
	) {}
}
