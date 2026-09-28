<?php

namespace Webatvantage\Bpost\Api\Shm\DataObjects\ProductConfiguration;

use SimpleXMLElement;
use Webatvantage\Bpost\Api\Contracts\XmlDeserializable;
use Webatvantage\Bpost\Api\Shm\Enums\Product as ProductName;

/**
 * What this account is configured to sell: delivery methods, products, prices and options.
 */
class ProductConfiguration implements XmlDeserializable
{
	/**
	 * @param array<int, DeliveryMethod> $deliveryMethods
	 */
	public function __construct(public private(set) array $deliveryMethods = []) {}

	public static function fromXml(SimpleXMLElement $xml): static
	{
		$methods = [];

		foreach ($xml->deliveryMethod ?? [] as $method)
		{
			$methods[] = DeliveryMethod::fromXml($method);
		}

		return new static($methods);
	}

	/**
	 * Whether an order may name this product at all.
	 *
	 * @return array<int, Product>
	 */
	public function products(): array
	{
		return array_merge(...array_map(fn (DeliveryMethod $m) => $m->products, $this->deliveryMethods));
	}

	public function offers(ProductName $product): bool
	{
		foreach ($this->products() as $configured)
		{
			if ($configured->name === $product)
			{
				return true;
			}
		}

		return false;
	}
}
