<?php

namespace Webatvantage\Bpost\Api\Parcel\DataObjects;

use Dom\Element;
use Webatvantage\Bpost\Api\Contracts\XmlDeserializable;
use Webatvantage\Bpost\Api\Support\Xml;

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

	public static function fromXml(Element $xml): static
	{
		return new static(
			Xml::text($xml, 'id'),
			Xml::text($xml, 'name'),
			Xml::text($xml, 'streetName'),
			Xml::text($xml, 'houseNumber'),
			Xml::text($xml, 'postalCode'),
			Xml::text($xml, 'city'),
		);
	}
}
