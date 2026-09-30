<?php

namespace Webatvantage\Bpost\Api\Geo\DataObjects;

use Webatvantage\Bpost\Api\Contracts\XmlDeserializable;
use Webatvantage\Bpost\Api\Exceptions\UnexpectedValueException;
use Webatvantage\Bpost\Api\Geo\Enums\PointType;
use Webatvantage\Bpost\Api\Support\XmlElement;

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
	 * A copy of this point carrying bpost's details page for it.
	 *
	 * A nearest-points search answers the URL beside the record rather than inside it, so the
	 * point has to be read before the URL can be built from its id and type.
	 */
	public function withPageUrl(?string $pageUrl): static
	{
		return clone($this, ['pageUrl' => $pageUrl]);
	}

	/**
	 * @param XmlElement $xml The record element, whose children are Id, Type, Name and so on
	 * @param float|null $distance Metres from the searched address; only a nearest-points search has one
	 * @param string|null $pageUrl The HTML details page bpost links to, when the response carried one
	 *
	 * @throws UnexpectedValueException
	 */
	public static function fromXml(XmlElement $xml, ?float $distance = null, ?string $pageUrl = null): static
	{
		$services = [];
		$serviceList = $xml->child('Services');

		if ($serviceList !== null)
		{
			foreach ($serviceList->children('Service') as $service)
			{
				$services[] = Service::fromXml($service);
			}
		}

		$typeCode = $xml->text('Type');
		$hours = $xml->child('Hours');
		$attributes = $xml->child('Attributes');

		return new static(
			id: $xml->text('Id', 'ID') ?? '',
			type: isset($typeCode) ? PointType::tryFrom((int)$typeCode) : null,
			name: $xml->text('Name', 'OFFICE'),
			street: $xml->text('Street', 'STREET'),
			number: $xml->text('Number', 'NR'),
			boxNumber: $xml->text('BoxNumber', 'BOXNR'),
			zip: $xml->text('Zip', 'ZIP'),
			city: $xml->text('City', 'CITY'),
			country: $xml->text('Country', 'COUNTRY'),
			latitude: static::float($xml->text('Latitude')),
			longitude: static::float($xml->text('Longitude')),
			x: static::int($xml->text('X')),
			y: static::int($xml->text('Y')),
			closedFrom: $xml->text('ClosedFrom'),
			closedTo: $xml->text('ClosedTo'),
			note: $xml->text('Note', 'NOTE'),
			services: $services,
			openingHours: $hours === null ? new OpeningHours() : OpeningHours::fromXml($hours),
			attributes: $attributes === null ? new Attributes() : Attributes::fromXml($attributes),
			distance: $distance,
			pageUrl: $pageUrl,
		);
	}

	protected static function float(?string $value): ?float
	{
		return isset($value) ? (float)$value : null;
	}

	protected static function int(?string $value): ?int
	{
		return isset($value) ? (int)$value : null;
	}
}
