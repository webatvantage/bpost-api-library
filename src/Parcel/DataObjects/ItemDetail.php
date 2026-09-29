<?php

namespace Webatvantage\Bpost\Api\Parcel\DataObjects;

use Webatvantage\Bpost\Api\Contracts\XmlDeserializable;
use Webatvantage\Bpost\Api\Support\XmlElement;

/**
 * What bpost recorded about the parcel itself.
 */
class ItemDetail implements XmlDeserializable
{
	public function __construct(public private(set) ?int $weightInGrams = null, public private(set) ?string $type = null) {}

	public static function fromXml(XmlElement $xml): static
	{
		$weightInGrams = $xml->text('weightInGrams');

		return new static(
			$weightInGrams === null ? null : (int)$weightInGrams,
			$xml->text('type'),
		);
	}
}
