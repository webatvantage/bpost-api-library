<?php

namespace Webatvantage\Bpost\Api\Geo\Requests;

use Webatvantage\Bpost\Api\ApiAdapter\HttpApiAdapter;
use Webatvantage\Bpost\Api\Enums\Language;
use Webatvantage\Bpost\Api\Exceptions\UnexpectedValueException;
use Webatvantage\Bpost\Api\Geo\DataObjects\ServicePoint;
use Webatvantage\Bpost\Api\Geo\Enums\PointType;
use Webatvantage\Bpost\Api\Geo\Exceptions\LocatorException;
use Webatvantage\Bpost\Api\Geo\GeoApiConfig;
use Webatvantage\Bpost\Api\Geo\Traits\HasAttributeFilter;

/**
 * `Function=getallservicepoints` — every point in a country, optionally narrowed by type and zip.
 *
 * This is the one operation that can answer more than 10 MB, which is why the client always sends
 * `Accept-Encoding: gzip`; without it bpost truncates the download.
 *
 * Note it identifies the caller with `Account` rather than `Partner`, though the value is the same.
 */
class AllServicePointsRequest extends GeoRequest
{
	use HasAttributeFilter;

	public function __construct(HttpApiAdapter $apiAdapter, GeoApiConfig $config)
	{
		parent::__construct($apiAdapter, [
			'Function' => 'getallservicepoints',
			'Account' => $config->partner,
			'Language' => Language::NL->value,
			'Country' => 'BE',
		]);
	}

	public function country(string $country): static
	{
		return $this->addParameter('Country', strtoupper($country));
	}

	/**
	 * Narrow to one kind of point. Left out, bpost returns every type.
	 */
	public function type(PointType $type): static
	{
		return $this->addParameter('Type', $type);
	}

	/**
	 * Narrow to one postal code. Left out, bpost returns the whole country.
	 */
	public function zip(string $zip): static
	{
		return $this->addParameter('Zip', $zip);
	}

	/**
	 * @return array<ServicePoint>
	 *
	 * @throws UnexpectedValueException
	 * @throws LocatorException
	 */
	public function get(): array
	{
		$xml = $this->send();
		$points = [];

		$list = $xml->child('PickupPointList');

		foreach ($list === null ? [] : $list->children('Point') as $point)
		{
			$points[] = ServicePoint::fromXml($point);
		}

		return $points;
	}
}
