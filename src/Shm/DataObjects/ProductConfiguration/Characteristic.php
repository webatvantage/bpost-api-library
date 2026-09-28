<?php

namespace Webatvantage\Bpost\Api\Shm\DataObjects\ProductConfiguration;

use SimpleXMLElement;
use Webatvantage\Bpost\Api\Contracts\XmlDeserializable;

/**
 * One value an option can take, such as a warranty band.
 */
class Characteristic implements XmlDeserializable
{
	public function __construct(
		public private(set) ?string $name = null,
		public private(set) ?string $displayValue = null,
		public private(set) ?int $value = null,
	) {}

	public static function fromXml(SimpleXMLElement $xml): static
	{
		return new static(
			isset($xml['name']) ? (string)$xml['name'] : null,
			isset($xml['displayValue']) ? (string)$xml['displayValue'] : null,
			isset($xml['value']) ? (int)$xml['value'] : null,
		);
	}
}
