<?php

namespace Webatvantage\Bpost\Api\Parcel\DataObjects;

use SimpleXMLElement;
use Webatvantage\Bpost\Api\Contracts\XmlDeserializable;

/**
 * What bpost recorded about the parcel itself.
 */
class ItemDetail implements XmlDeserializable
{
	public function __construct(public private(set) ?int $weightInGrams = null, public private(set) ?string $type = null) {}

	public static function fromXml(SimpleXMLElement $xml): static
	{
		return new static(
			isset($xml->weightInGrams) && trim((string)$xml->weightInGrams) !== ''
				? (int)$xml->weightInGrams
				: null,
			isset($xml->type) && trim((string)$xml->type) !== '' ? trim((string)$xml->type) : null,
		);
	}
}
