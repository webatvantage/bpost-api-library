<?php

namespace Webatvantage\Bpost\Api\Tests\Geo\Requests;

use Webatvantage\Bpost\Api\Geo\Enums\PointType;
use Webatvantage\Bpost\Api\Tests\Geo\GeoTestCase;

class AllServicePointsRequestTest extends GeoTestCase
{
	private const RESPONSE = <<<'XML'
		<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
		<TaxipostLocator version="1.0" type="TaxipostLocatorList">
			<PickupPointList>
				<Point>
					<Id>014472</Id>
					<Type>4</Type>
					<Name>WIJNEGEM</Name>
					<Street>Turnhoutsebaan</Street>
					<Number>468</Number>
					<Zip>2110</Zip>
					<City>Wijnegem</City>
				</Point>
				<Point>
					<Id>042599</Id>
					<Type>4</Type>
					<Name>LAKEN</Name>
					<Zip>1020</Zip>
					<City>BRUXELLES</City>
				</Point>
			</PickupPointList>
		</TaxipostLocator>
		XML;

	/**
	 * This operation identifies the caller with Account, not Partner.
	 */
	public function test_it_sends_the_account_parameter()
	{
		$this->mockResponse(200, self::RESPONSE);

		$this->client()->servicePoints()->all()->get();

		$query = $this->sentQuery();

		$this->assertSame('getallservicepoints', $query['Function']);
		$this->assertSame('999999', $query['Account']);
		$this->assertArrayNotHasKey('Partner', $query);
	}

	public function test_it_narrows_by_country_type_and_zip()
	{
		$this->mockResponse(200, self::RESPONSE);

		$this->client()->servicePoints()->all()
			->country('nl')
			->type(PointType::ParcelLocker)
			->zip('4811')
			->get();

		$query = $this->sentQuery();

		$this->assertSame('NL', $query['Country']);
		$this->assertSame('4', $query['Type']);
		$this->assertSame('4811', $query['Zip']);
	}

	public function test_it_omits_type_and_zip_when_not_narrowed()
	{
		$this->mockResponse(200, self::RESPONSE);

		$this->client()->servicePoints()->all()->get();

		$query = $this->sentQuery();

		$this->assertArrayNotHasKey('Type', $query);
		$this->assertArrayNotHasKey('Zip', $query);
	}

	public function test_it_maps_points_that_are_not_wrapped_in_a_record()
	{
		$this->mockResponse(200, self::RESPONSE);

		$points = $this->client()->servicePoints()->all()->get();

		$this->assertCount(2, $points);
		$this->assertSame('014472', $points[0]->id);
		$this->assertSame('WIJNEGEM', $points[0]->name);
		$this->assertSame(PointType::ParcelLocker, $points[1]->type);
	}
}
