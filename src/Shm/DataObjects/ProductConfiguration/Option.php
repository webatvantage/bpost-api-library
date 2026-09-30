<?php

namespace Webatvantage\Bpost\Api\Shm\DataObjects\ProductConfiguration;

use Webatvantage\Bpost\Api\Contracts\XmlDeserializable;
use Webatvantage\Bpost\Api\Shm\Enums\OptionVisibility;
use Webatvantage\Bpost\Api\Support\XmlElement;

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

	public static function fromXml(XmlElement $xml): static
	{
		$characteristics = [];

		// bpost spells it "chracteristic" in the response. Mirrored rather than corrected.
		foreach ($xml->children('chracteristic') as $characteristic)
		{
			$characteristics[] = Characteristic::fromXml($characteristic);
		}

		return new static(
			name: $xml->attribute('name'),
			price: $xml->integerAttribute('price'),
			visibility: // And "visiblity" here. Also bpost's own spelling.
			OptionVisibility::tryFrom($xml->attribute('visiblity') ?? ''),
			characteristics: $characteristics,
		);
	}
}
