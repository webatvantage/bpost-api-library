<?php

namespace Webatvantage\Bpost\Api\Shm\DataObjects\ProductConfiguration;

use Webatvantage\Bpost\Api\Contracts\XmlDeserializable;
use Webatvantage\Bpost\Api\Shm\Enums\Product as ProductName;
use Webatvantage\Bpost\Api\Support\XmlElement;

/**
 * What this account is configured to sell: delivery methods, products, prices and options.
 */
class ProductConfiguration implements XmlDeserializable
{
	/**
	 * @param array<int, DeliveryMethod> $deliveryMethods
	 */
	public function __construct(public private(set) array $deliveryMethods = []) {}

	public static function fromXml(XmlElement $xml): static
	{
		$methods = [];

		foreach ($xml->children('deliveryMethod') as $method)
		{
			$methods[] = DeliveryMethod::fromXml($method);
		}

		return new static($methods);
	}

	/**
	 * Every product across every delivery method.
	 *
	 * The same product appears more than once when it is offered under several of them.
	 *
	 * @return array<int, Product>
	 */
	public function products(): array
	{
		return array_merge(...array_map(fn (DeliveryMethod $method) => $method->products, $this->deliveryMethods));
	}

	/**
	 * Whether an order may name this product at all.
	 */
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
