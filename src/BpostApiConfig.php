<?php

namespace Webatvantage\Bpost\Api;

use Webatvantage\Bpost\Api\Geo\GeoApiConfig;

/**
 * Credentials for the bpost services you use.
 *
 * Every service is optional: bpost issues their credentials separately and most integrations only
 * hold one or two sets. Reaching an unconfigured service throws rather than failing at the HTTP
 * layer with something unhelpful.
 */
readonly class BpostApiConfig
{
	public function __construct(public ?GeoApiConfig $geo = null) {}
}
