<?php

namespace Webatvantage\Bpost\Api\Tests\Shm\Requests;

use Webatvantage\Bpost\Api\Shm\Enums\DeliveryMethod;
use Webatvantage\Bpost\Api\Shm\Enums\Product;
use Webatvantage\Bpost\Api\Shm\ShmApiClient;
use Webatvantage\Bpost\Api\Tests\Shm\ShmTestCase;

/**
 * Reads bpost's own Get Product configuration response.
 *
 * This subtree had no test coverage at all in 3.x, so it is worth pinning against a real document
 * before anything relies on it.
 */
class ProductConfigurationResponseTest extends ShmTestCase
{
	private function fetch()
	{
		$this->mockResponse(200, $this->fixture('product-configuration.xml'));

		return new ShmApiClient($this->config(), ['handler' => $this->handlerStack()])
			->productConfiguration()
			->get();
	}

	public function test_it_reads_the_delivery_methods()
	{
		$configuration = $this->fetch();

		$this->assertNotEmpty($configuration->deliveryMethods);

		$names = array_map(fn ($method) => $method->name, $configuration->deliveryMethods);

		$this->assertContains(DeliveryMethod::HomeOrOffice, $names);
		$this->assertContains(DeliveryMethod::PickupPoint, $names);
		$this->assertContains(DeliveryMethod::ParcelLocker, $names);
	}

	public function test_it_reads_the_products_under_each_method()
	{
		$configuration = $this->fetch();

		$this->assertTrue($configuration->offers(Product::Bpack24hPro));
		$this->assertTrue($configuration->offers(Product::BpackAtBpost));
		$this->assertTrue($configuration->offers(Product::Bpack247));
		$this->assertTrue($configuration->offers(Product::BpackEuropeBusiness));
	}

	public function test_it_reads_prices_banded_by_weight()
	{
		$product = null;

		foreach ($this->fetch()->products() as $candidate)
		{
			if ($candidate->name === Product::Bpack247)
			{
				$product = $candidate;
			}
		}

		$this->assertNotNull($product);

		$price = $product->priceFor('BE');

		$this->assertNotNull($price);
		$this->assertSame(400, $price->priceLessThan2);
		$this->assertSame(400, $price->forWeight(1500));
		$this->assertSame(400, $price->forWeight(25000));
	}

	/**
	 * 3.x compared grams and then reported them as kilograms: "Invalid weight (35000 kg),
	 * maximum is 30".
	 */
	public function test_an_overweight_parcel_is_reported_in_the_unit_it_was_given()
	{
		$product = null;

		foreach ($this->fetch()->products() as $candidate)
		{
			if ($candidate->name === Product::Bpack247)
			{
				$product = $candidate;
			}
		}

		$this->expectException(\Webatvantage\Bpost\Api\Exceptions\InvalidValueException::class);
		$this->expectExceptionMessage('grams');

		$product->priceFor('BE')->forWeight(35000);
	}
}
