<?php

namespace Webatvantage\Bpost\Api\Parcel\DataObjects;

use SimpleXMLElement;
use Webatvantage\Bpost\Api\Contracts\XmlDeserializable;

/**
 * Where a parcel is waiting, when tracking says it is awaiting collection.
 */
class PickupPoint implements XmlDeserializable
{
	public function __construct(
		public private(set) ?string $id = null,
		public private(set) ?string $name = null,
		public private(set) ?string $streetName = null,
		public private(set) ?string $houseNumber = null,
		public private(set) ?string $postalCode = null,
		public private(set) ?string $city = null,
	) {}

	public static function fromXml(SimpleXMLElement $xml): static
	{
		$value = static fn (string $name): ?string => isset($xml->{$name}) && trim((string)$xml->{$name}) !== ''
			? trim((string)$xml->{$name})
			: null;

		return new static(
			$value('id'),
			$value('name'),
			$value('streetName'),
			$value('houseNumber'),
			$value('postalCode'),
			$value('city'),
		);
	}
}
