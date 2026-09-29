<?php

namespace Webatvantage\Bpost\Api\Shm\DataObjects\ProductConfiguration;

use Webatvantage\Bpost\Api\Contracts\XmlDeserializable;
use Webatvantage\Bpost\Api\Support\XmlElement;

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

	public static function fromXml(XmlElement $xml): static
	{
		return new static(
			$xml->attribute('name'),
			$xml->attribute('displayValue'),
			$xml->integerAttribute('value'),
		);
	}
}
