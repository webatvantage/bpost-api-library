<?php

namespace Webatvantage\Bpost\Api\Shm\DataObjects\ProductConfiguration;

use Dom\Element;
use Webatvantage\Bpost\Api\Contracts\XmlDeserializable;
use Webatvantage\Bpost\Api\Shm\Enums\OptionVisibility;
use Webatvantage\Bpost\Api\Shm\Support\Xml;

/**
 * An option offered on a product, and what it costs.
 */
class Option implements XmlDeserializable
{
	/**
	 * @param array<int, Characteristic> $characteristics
	 */
	public function __construct(
		public private(set) ?string $name = null,
		public private(set) ?int $price = null,
		public private(set) ?OptionVisibility $visibility = null,
		public private(set) array $characteristics = [],
	) {}

	public static function fromXml(Element $xml): static
	{
		$characteristics = [];

		// bpost spells it "chracteristic" in the response. Mirrored rather than corrected.
		foreach (Xml::children($xml, 'chracteristic') as $characteristic)
		{
			$characteristics[] = Characteristic::fromXml($characteristic);
		}

		return new static(
			Xml::attribute($xml, 'name'),
			Xml::integerAttribute($xml, 'price'),
			// And "visiblity" here. Also bpost's own spelling.
			OptionVisibility::tryFrom(Xml::attribute($xml, 'visiblity') ?? ''),
			$characteristics,
		);
	}
}
