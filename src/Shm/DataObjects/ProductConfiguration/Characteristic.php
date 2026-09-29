<?php

namespace Webatvantage\Bpost\Api\Shm\DataObjects\ProductConfiguration;

use Dom\Element;
use Webatvantage\Bpost\Api\Contracts\XmlDeserializable;
use Webatvantage\Bpost\Api\Shm\Support\Xml;

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

	public static function fromXml(Element $xml): static
	{
		return new static(
			Xml::attribute($xml, 'name'),
			Xml::attribute($xml, 'displayValue'),
			Xml::integerAttribute($xml, 'value'),
		);
	}
}
