<?php

namespace Webatvantage\Bpost\Api\Parcel\DataObjects;

use Dom\Element;
use Webatvantage\Bpost\Api\Contracts\XmlDeserializable;
use Webatvantage\Bpost\Api\Support\Xml;

/**
 * What bpost recorded about the parcel itself.
 */
class ItemDetail implements XmlDeserializable
{
	public function __construct(public private(set) ?int $weightInGrams = null, public private(set) ?string $type = null) {}

	public static function fromXml(Element $xml): static
	{
		$weightInGrams = Xml::text($xml, 'weightInGrams');

		return new static(
			$weightInGrams === null ? null : (int)$weightInGrams,
			Xml::text($xml, 'type'),
		);
	}
}
