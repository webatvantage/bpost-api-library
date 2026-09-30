<?php

namespace Webatvantage\Bpost\Api\Shm\DataObjects\ProductConfiguration;

use Webatvantage\Bpost\Api\Contracts\XmlDeserializable;
use Webatvantage\Bpost\Api\Exceptions\InvalidValueException;
use Webatvantage\Bpost\Api\Shm\DataObjects\Box\DeliveryBox;
use Webatvantage\Bpost\Api\Support\XmlElement;

/**
 * What a product costs to one country, banded by weight.
 *
 * Every price is in euro cents.
 */
class Price implements XmlDeserializable
{
	public function __construct(
		public private(set) ?string $countryIso2Code = null,
		public private(set) ?int $priceLessThan2 = null,
		public private(set) ?int $price2To5 = null,
		public private(set) ?int $price5To10 = null,
		public private(set) ?int $price10To20 = null,
		public private(set) ?int $price20To30 = null,
	) {}

	public static function fromXml(XmlElement $xml): static
	{
		return new static(
			countryIso2Code: $xml->attribute('countryIso2Code'),
			priceLessThan2: $xml->integerAttribute('priceLessThan2'),
			price2To5: $xml->integerAttribute('price2To5'),
			price5To10: $xml->integerAttribute('price5To10'),
			price10To20: $xml->integerAttribute('price10To20'),
			price20To30: $xml->integerAttribute('price20To30'),
		);
	}

	/**
	 * @param int $weight In grams, matching the unit used everywhere else
	 *
	 * @throws InvalidValueException
	 */
	public function forWeight(int $weight): ?int
	{
		return match (true)
		{
			$weight <= 2_000 => $this->priceLessThan2,
			$weight <= 5_000 => $this->price2To5,
			$weight <= 10_000 => $this->price5To10,
			$weight <= 20_000 => $this->price10To20,
			$weight <= DeliveryBox::MAX_WEIGHT => $this->price20To30,
			default => throw new InvalidValueException('weight', $weight, [
				sprintf('0 to %d grams', DeliveryBox::MAX_WEIGHT),
			]),
		};
	}
}
