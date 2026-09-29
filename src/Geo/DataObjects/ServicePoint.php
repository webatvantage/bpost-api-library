<?php

namespace Webatvantage\Bpost\Api\Geo\DataObjects;

use Dom\Element;
use Webatvantage\Bpost\Api\Contracts\XmlDeserializable;
use Webatvantage\Bpost\Api\Geo\Enums\PointType;
use Webatvantage\Bpost\Api\Support\Xml;

/**
 * A bpost pick-up point: post office, post point, parcel point, parcel locker or Click & Collect
 * shop.
 *
 * The Geolocator spells the same field differently per operation — a nearest-points search answers
 * `<Name>`/`<Street>`/`<Zip>` while a details lookup answers `<OFFICE>`/`<STREET>`/`<ZIP>` — so
 * every field is read under both spellings.
 */
class ServicePoint implements XmlDeserializable
{
	/**
	 * @param array<Service> $services
	 */
	public function __construct(
		public readonly string $id,
		public readonly ?PointType $type = null,
		public readonly ?string $name = null,
		public readonly ?string $street = null,
		public readonly ?string $number = null,
		public readonly ?string $boxNumber = null,
		public readonly ?string $zip = null,
		public readonly ?string $city = null,
		public readonly ?string $country = null,
		public readonly ?float $latitude = null,
		public readonly ?float $longitude = null,
		public readonly ?int $x = null,
		public readonly ?int $y = null,
		public readonly ?string $closedFrom = null,
		public readonly ?string $closedTo = null,
		public readonly ?string $note = null,
		public readonly array $services = [],
		public readonly OpeningHours $openingHours = new OpeningHours(),
		public readonly Attributes $attributes = new Attributes(),
		public readonly ?float $distance = null,
		public private(set) ?string $pageUrl = null,
	) {}

	public function withPageUrl(?string $pageUrl): static
	{
		$this->pageUrl = $pageUrl;

		return $this;
	}

	/**
	 * @param Element $xml The record element, whose children are Id, Type, Name and so on
	 * @param float|null $distance Metres from the searched address; only a nearest-points search has one
	 * @param string|null $pageUrl The HTML details page bpost links to, when the response carried one
	 */
	public static function fromXml(Element $xml, ?float $distance = null, ?string $pageUrl = null): static
	{
		$services = [];
		$serviceList = Xml::child($xml, 'Services');

		if ($serviceList !== null)
		{
			foreach (Xml::children($serviceList, 'Service') as $service)
			{
				$services[] = Service::fromXml($service);
			}
		}

		$typeCode = Xml::text($xml, 'Type');
		$hours = Xml::child($xml, 'Hours');
		$attributes = Xml::child($xml, 'Attributes');

		return new static(
			id: Xml::text($xml, 'Id', 'ID') ?? '',
			type: $typeCode === null ? null : PointType::tryFrom((int)$typeCode),
			name: Xml::text($xml, 'Name', 'OFFICE'),
			street: Xml::text($xml, 'Street', 'STREET'),
			number: Xml::text($xml, 'Number', 'NR'),
			boxNumber: Xml::text($xml, 'BoxNumber', 'BOXNR'),
			zip: Xml::text($xml, 'Zip', 'ZIP'),
			city: Xml::text($xml, 'City', 'CITY'),
			country: Xml::text($xml, 'Country', 'COUNTRY'),
			latitude: self::float(Xml::text($xml, 'Latitude')),
			longitude: self::float(Xml::text($xml, 'Longitude')),
			x: self::int(Xml::text($xml, 'X')),
			y: self::int(Xml::text($xml, 'Y')),
			closedFrom: Xml::text($xml, 'ClosedFrom'),
			closedTo: Xml::text($xml, 'ClosedTo'),
			note: Xml::text($xml, 'Note', 'NOTE'),
			services: $services,
			openingHours: $hours === null ? new OpeningHours() : OpeningHours::fromXml($hours),
			attributes: $attributes === null ? new Attributes() : Attributes::fromXml($attributes),
			distance: $distance,
			pageUrl: $pageUrl,
		);
	}

	private static function float(?string $value): ?float
	{
		return $value === null ? null : (float)$value;
	}

	private static function int(?string $value): ?int
	{
		return $value === null ? null : (int)$value;
	}
}
