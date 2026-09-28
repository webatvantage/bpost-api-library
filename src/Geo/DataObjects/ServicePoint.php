<?php

namespace Webatvantage\Bpost\Api\Geo\DataObjects;

use SimpleXMLElement;
use Webatvantage\Bpost\Api\Contracts\XmlDeserializable;
use Webatvantage\Bpost\Api\Geo\Enums\PointType;

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
		public readonly ?string $pageUrl = null,
	) {}

	/**
	 * @param SimpleXMLElement $xml The record element, whose children are Id, Type, Name and so on
	 * @param float|null $distance Metres from the searched address; only a nearest-points search has one
	 * @param string|null $pageUrl The HTML details page bpost links to, when the response carried one
	 */
	public static function fromXml(SimpleXMLElement $xml, ?float $distance = null, ?string $pageUrl = null): static
	{
		$services = [];

		if (isset($xml->Services->Service))
		{
			foreach ($xml->Services->Service as $service)
			{
				$services[] = Service::fromXml($service);
			}
		}

		$typeCode = self::value($xml, 'Type');

		return new static(
			id: (string)(self::value($xml, 'Id', 'ID') ?? ''),
			type: $typeCode === null ? null : PointType::tryFrom((int)$typeCode),
			name: self::value($xml, 'Name', 'OFFICE'),
			street: self::value($xml, 'Street', 'STREET'),
			number: self::value($xml, 'Number', 'NR'),
			boxNumber: self::value($xml, 'BoxNumber'),
			zip: self::value($xml, 'Zip', 'ZIP'),
			city: self::value($xml, 'City', 'CITY'),
			country: self::value($xml, 'Country', 'COUNTRY'),
			latitude: self::float($xml, 'Latitude'),
			longitude: self::float($xml, 'Longitude'),
			x: self::int($xml, 'X'),
			y: self::int($xml, 'Y'),
			closedFrom: self::value($xml, 'ClosedFrom'),
			closedTo: self::value($xml, 'ClosedTo'),
			note: self::value($xml, 'Note', 'NOTE'),
			services: $services,
			openingHours: isset($xml->Hours) ? OpeningHours::fromXml($xml->Hours) : new OpeningHours(),
			attributes: isset($xml->Attributes) ? Attributes::fromXml($xml->Attributes) : new Attributes(),
			distance: $distance,
			pageUrl: $pageUrl,
		);
	}

	private static function value(SimpleXMLElement $xml, string ...$names): ?string
	{
		foreach ($names as $name)
		{
			if (!isset($xml->{$name}))
			{
				continue;
			}

			$value = trim((string)$xml->{$name});

			if ($value !== '')
			{
				return $value;
			}
		}

		return null;
	}

	private static function float(SimpleXMLElement $xml, string $name): ?float
	{
		$value = self::value($xml, $name);

		return $value === null ? null : (float)$value;
	}

	private static function int(SimpleXMLElement $xml, string $name): ?int
	{
		$value = self::value($xml, $name);

		return $value === null ? null : (int)$value;
	}
}
