<?php

namespace Webatvantage\Bpost\Api\Shm\DataObjects\ProductConfiguration;

use Dom\Element;
use Webatvantage\Bpost\Api\Contracts\XmlDeserializable;
use Webatvantage\Bpost\Api\Shm\Enums\DeliveryMethod as DeliveryMethodName;
use Webatvantage\Bpost\Api\Shm\Enums\Visibility;
use Webatvantage\Bpost\Api\Shm\Support\Xml;

/**
 * One delivery method, and the products offered under it.
 *
 * The same method name appears more than once in a response: once for national products and again
 * for international ones.
 */
class DeliveryMethod implements XmlDeserializable
{
	/**
	 * @param array<int, Product> $products
	 */
	public function __construct(
		public private(set) ?DeliveryMethodName $name = null,
		public private(set) ?Visibility $visibility = null,
		public private(set) array $products = [],
	) {}

	public static function fromXml(Element $xml): static
	{
		$products = [];

		foreach (Xml::children($xml, 'product') as $product)
		{
			$products[] = Product::fromXml($product);
		}

		return new static(
			DeliveryMethodName::tryFrom(Xml::attribute($xml, 'name') ?? ''),
			// bpost's own spelling of "visibility".
			Visibility::tryFrom(Xml::attribute($xml, 'visiblity') ?? ''),
			$products,
		);
	}

	public function isVisible(): bool
	{
		return $this->visibility === Visibility::Visible;
	}
}
