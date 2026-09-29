<?php

namespace Webatvantage\Bpost\Api\Shm\DataObjects\ProductConfiguration;

use Webatvantage\Bpost\Api\Contracts\XmlDeserializable;
use Webatvantage\Bpost\Api\Shm\Enums\Product as ProductName;
use Webatvantage\Bpost\Api\Support\XmlElement;

/**
 * A product this account may use, with its prices and options.
 */
class Product implements XmlDeserializable
{
	/**
	 * @param array<int, Price> $prices
	 * @param array<int, Option> $options
	 */
	public function __construct(
		public private(set) ?ProductName $name = null,
		public private(set) bool $default = false,
		public private(set) array $prices = [],
		public private(set) array $options = [],
	) {}

	public static function fromXml(XmlElement $xml): static
	{
		$prices = [];
		$options = [];

		foreach ($xml->children('price') as $price)
		{
			$prices[] = Price::fromXml($price);
		}

		foreach ($xml->children('option') as $option)
		{
			$options[] = Option::fromXml($option);
		}

		return new static(
			ProductName::tryFrom($xml->attribute('name') ?? ''),
			$xml->attribute('default') === 'true',
			$prices,
			$options,
		);
	}

	public function priceFor(string $countryIso2Code): ?Price
	{
		foreach ($this->prices as $price)
		{
			if ($price->countryIso2Code === strtoupper($countryIso2Code))
			{
				return $price;
			}
		}

		return null;
	}
}
