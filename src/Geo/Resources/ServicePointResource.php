<?php

namespace Webatvantage\Bpost\Api\Geo\Resources;

use Webatvantage\Bpost\Api\ApiAdapter\HttpApiAdapter;
use Webatvantage\Bpost\Api\Contracts\Resource;
use Webatvantage\Bpost\Api\Geo\Enums\PointType;
use Webatvantage\Bpost\Api\Geo\GeoApiConfig;
use Webatvantage\Bpost\Api\Geo\Requests\AllServicePointsRequest;
use Webatvantage\Bpost\Api\Geo\Requests\NearestServicePointsRequest;
use Webatvantage\Bpost\Api\Geo\Requests\ServicePointDetailsRequest;
use Webatvantage\Bpost\Api\Geo\Requests\ServicePointPageRequest;

/**
 * The four Geolocator operations.
 *
 * Each returns a request you keep narrowing and then call get() on, so the optional parameters read
 * as a sentence instead of a positional argument list nobody can remember the order of.
 */
class ServicePointResource extends Resource
{
	public function __construct(HttpApiAdapter $apiAdapter, private readonly GeoApiConfig $config)
	{
		parent::__construct($apiAdapter);
	}

	/**
	 * Points nearest an address. `$zone` is the postal code and/or city, and is mandatory.
	 */
	public function nearest(string $zone, ?string $street = null, ?string $number = null): NearestServicePointsRequest
	{
		return new NearestServicePointsRequest($this->apiAdapter, $this->config, $zone, $street, $number);
	}

	public function details(string $id, PointType $type): ServicePointDetailsRequest
	{
		return new ServicePointDetailsRequest($this->apiAdapter, $this->config, $id, $type);
	}

	public function all(): AllServicePointsRequest
	{
		return new AllServicePointsRequest($this->apiAdapter, $this->config);
	}

	/**
	 * The URL of bpost's own HTML details page for a point, for embedding in a map or an iframe.
	 */
	public function page(string $id, PointType $type): ServicePointPageRequest
	{
		return new ServicePointPageRequest($this->config, $id, $type);
	}

	public function pageUrl(string $id, PointType $type): string
	{
		return $this->page($id, $type)->toUrl($this->config->baseUri);
	}
}
