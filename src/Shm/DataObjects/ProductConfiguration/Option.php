<?php

namespace Webatvantage\Bpost\Api\Shm\DataObjects\ProductConfiguration;

use SimpleXMLElement;
use Webatvantage\Bpost\Api\Contracts\XmlDeserializable;
use Webatvantage\Bpost\Api\Shm\Enums\OptionVisibility;

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

	public static function fromXml(SimpleXMLElement $xml): static
	{
		$characteristics = [];

		// bpost spells it "chracteristic" in the response. Mirrored rather than corrected.
		foreach ($xml->chracteristic ?? [] as $characteristic)
		{
			$characteristics[] = Characteristic::fromXml($characteristic);
		}

		return new static(
			isset($xml['name']) ? (string)$xml['name'] : null,
			isset($xml['price']) ? (int)$xml['price'] : null,
			// And "visiblity" here. Also bpost's own spelling.
			isset($xml['visiblity']) ? OptionVisibility::tryFrom((string)$xml['visiblity']) : null,
			$characteristics,
		);
	}
}
