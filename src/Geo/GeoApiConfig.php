<?php

namespace Webatvantage\Bpost\Api\Geo;

readonly class GeoApiConfig
{
	/**
	 * @param string $partner Your bpost account id, activated for the Geolocator. bpost sends it as
	 *                        `Partner` on most operations and as `Account` on Get All Service
	 *                        Points, but it is the same identifier
	 * @param string|null $apiKey The `x-api-key` bpost issues per account. Mandatory on every
	 *                           request to the pudo.bpost.cloud domain since manual section
	 *                           B.4.1.0; the older pudo.bpost.be ignores it, so an integration
	 *                           still pointed there has none to send
	 * @param string|null $appId Optional four-character application id, used by bpost for statistics
	 * @param string $baseUri
	 */
	public function __construct(
		public string $partner,
		public ?string $apiKey = null,
		public ?string $appId = null,
		public string $baseUri = 'https://pudo.bpost.cloud',
	) {}
}
