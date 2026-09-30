<?php

namespace Webatvantage\Bpost\Api\Parcel\DataObjects;

use Webatvantage\Bpost\Api\Contracts\XmlDeserializable;
use Webatvantage\Bpost\Api\Support\XmlElement;

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

	public static function fromXml(XmlElement $xml): static
	{
		return new static(
			id: $xml->text('id'),
			name: $xml->text('name'),
			streetName: $xml->text('streetName'),
			houseNumber: $xml->text('houseNumber'),
			postalCode: $xml->text('postalCode'),
			city: $xml->text('city'),
		);
	}
}
