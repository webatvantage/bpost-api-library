<?php

namespace Webatvantage\Bpost\Api\Geo\Requests;

use DateTimeInterface;
use Webatvantage\Bpost\Api\ApiAdapter\HttpApiAdapter;
use Webatvantage\Bpost\Api\Enums\Language;
use Webatvantage\Bpost\Api\Exceptions\UnexpectedValueException;
use Webatvantage\Bpost\Api\Geo\DataObjects\ServicePoint;
use Webatvantage\Bpost\Api\Geo\Enums\LockerType;
use Webatvantage\Bpost\Api\Geo\Enums\PointType;
use Webatvantage\Bpost\Api\Geo\GeoApiConfig;

/**
 * `Function=search` — the pick-up points nearest a given address.
 *
 * DD, CheckDate and CheckOpen are mandatory per manual B.4.1.1 and are filled in here, since
 * leaving them out returns points that cannot actually take a delivery on the day concerned.
 */
class NearestServicePointsRequest extends GeoRequest
{
	/** @var array<string> */
	private array $attributeFilters = [];

	public function __construct(
		HttpApiAdapter $apiAdapter,
		private readonly GeoApiConfig $config,
		string $zone,
		?string $street = null,
		?string $number = null,
	) {
		parent::__construct($apiAdapter, [
			'Function' => 'search',
			'Partner' => $config->partner,
			'AppId' => $config->appId,
			'Zone' => $zone,
			'Street' => $street,
			'Number' => $number,
			'Country' => 'BE',
			'Language' => Language::NL->value,
			'Type' => PointType::mask(PointType::PostOffice, PointType::PostPoint),
			'DD' => date('d-m-Y'),
			'CheckDate' => true,
			'CheckOpen' => true,
		]);
	}

	public function types(PointType ...$types): static
	{
		return $this->addParameter('Type', PointType::mask(...$types));
	}

	/**
	 * Country of the searched address. bpost accepts BE, FR and NL here.
	 */
	public function country(string $country): static
	{
		return $this->addParameter('Country', strtoupper($country));
	}

	public function limit(int $limit): static
	{
		return $this->addParameter('Limit', $limit);
	}

	/**
	 * The day the parcel is expected to be delivered; points closed then are left out.
	 */
	public function deliveryDate(DateTimeInterface $date): static
	{
		return $this->addParameter('DD', $date->format('d-m-Y'));
	}

	/**
	 * Return each point's known holiday closure.
	 */
	public function withHolidays(bool $include = true): static
	{
		return $this->addParameter('CheckList', $include);
	}

	/**
	 * Return opening hours and the other per-point details.
	 *
	 * Without this the response carries no `<Hours>`, so ServicePoint::$openingHours stays empty.
	 */
	public function withDetails(bool $include = true): static
	{
		return $this->addParameter('Info', $include);
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
	 *
	 * @throws UnexpectedValueException
	 */
	public function get(): array
	{
		$xml = $this->send();
		$points = [];

		$list = $xml->child('PoiList');

		foreach ($list === null ? [] : $list->children('Poi') as $entry)
		{
			$record = $entry->child('Record');

			if ($record === null)
			{
				continue;
			}

			$distance = $entry->text('Distance');
			$point = ServicePoint::fromXml($record, isset($distance) ? (float)$distance : null);

			$points[] = $point->withPageUrl($this->pageUrl($point));
		}

		return $points;
	}

	/**
	 * A search answers `<Info ServiceRef>`, a Function=info URL, so the page URL is built here.
	 */
	private function pageUrl(ServicePoint $point): ?string
	{
		if ($point->id === '' || $point->type === null)
		{
			return null;
		}

		return new ServicePointPageRequest($this->config, $point->id, $point->type)
			->toUrl($this->config->baseUri);
	}

	private function addAttributeFilter(string $filter): static
	{
		$this->attributeFilters[] = $filter;

		return $this->addParameter('AttributeFilter', $this->attributeFilters);
	}
}
