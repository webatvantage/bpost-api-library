<?php

namespace Webatvantage\Bpost\Api\Geo\Requests;

use Webatvantage\Bpost\Api\ApiAdapter\HttpApiAdapter;
use Webatvantage\Bpost\Api\Enums\Language;
use Webatvantage\Bpost\Api\Geo\DataObjects\ServicePoint;
use Webatvantage\Bpost\Api\Geo\Enums\LockerType;
use Webatvantage\Bpost\Api\Geo\Enums\PointType;
use Webatvantage\Bpost\Api\Geo\GeoApiConfig;
use Webatvantage\Bpost\Api\Support\Xml;

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
	/** @var array<string> */
	private array $attributeFilters = [];

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

	public function language(Language $language): static
	{
		return $this->addParameter('Language', $language);
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

	public function filterLockerType(LockerType $lockerType): static
	{
		return $this->addAttributeFilter('LOCKERTYPE:' . $lockerType->filterValue());
	}

	public function filterNightDelivery(bool $allowed = true): static
	{
		return $this->addAttributeFilter('NIGHTDELIVERY:' . ($allowed ? 'TRUE' : 'FALSE'));
	}

	/**
	 * @return array<ServicePoint>
	 */
	public function get(): array
	{
		$xml = $this->send();
		$points = [];

		$list = Xml::child($xml, 'PickupPointList');

		foreach ($list === null ? [] : Xml::children($list, 'Point') as $point)
		{
			$points[] = ServicePoint::fromXml($point);
		}

		return $points;
	}

	private function addAttributeFilter(string $filter): static
	{
		$this->attributeFilters[] = $filter;

		return $this->addParameter('AttributeFilter', $this->attributeFilters);
	}
}
