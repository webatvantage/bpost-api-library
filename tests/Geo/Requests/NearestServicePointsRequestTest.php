<?php

namespace Webatvantage\Bpost\Api\Tests\Geo\Requests;

use DateTimeImmutable;
use Webatvantage\Bpost\Api\Enums\Language;
use Webatvantage\Bpost\Api\Geo\Enums\LockerType;
use Webatvantage\Bpost\Api\Geo\Enums\PointType;
use Webatvantage\Bpost\Api\Geo\Exceptions\LocatorException;
use Webatvantage\Bpost\Api\Tests\Geo\GeoTestCase;

class NearestServicePointsRequestTest extends GeoTestCase
{
	private const RESPONSE = <<<'XML'
		<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
		<TaxipostLocator version="1.0" type="TaxipostLocatorList">
			<PoiList>
				<Poi>
					<Record>
						<Id>42599</Id>
						<Type>4</Type>
						<Name>DISTRIBUTEUR LAKEN DE WAND BPOST</Name>
						<Street>AVENUE DE LA BRISE</Street>
						<Number>13-15</Number>
						<BoxNumber>b</BoxNumber>
						<Zip>1020</Zip>
						<City>BRUXELLES</City>
						<Country>BE</Country>
						<Longitude>4.35852</Longitude>
						<Latitude>50.89749</Latitude>
						<Attributes>
							<Attribute>
								<AttributeCode>LOCKERTYPE</AttributeCode>
								<TextValue>CLASSIC</TextValue>
							</Attribute>
							<Attribute>
								<AttributeCode>NIGHTDELIVERY</AttributeCode>
								<TextValue>FALSE</TextValue>
							</Attribute>
						</Attributes>
						<Hours>
							<Monday>
								<AMOpen>00:01</AMOpen>
								<AMClose>23:59</AMClose>
								<PMOpen></PMOpen>
								<PMClose></PMClose>
							</Monday>
						</Hours>
					</Record>
					<Distance>547.81</Distance>
					<Info ServiceRef="https://pudo.bpost.cloud/Locator?Function=info&amp;Id=42599"/>
				</Poi>
			</PoiList>
		</TaxipostLocator>
		XML;

	public function test_it_sends_the_mandatory_parameters()
	{
		$this->mockResponse(200, self::RESPONSE);

		$this->client()->servicePoints()->nearest('1020', 'araucaria')->get();

		$query = $this->sentQuery();

		$this->assertSame('search', $query['Function']);
		$this->assertSame('999999', $query['Partner']);
		$this->assertSame('1020', $query['Zone']);
		$this->assertSame('1', $query['CheckDate']);
		$this->assertSame('1', $query['CheckOpen']);
		$this->assertMatchesRegularExpression('/^\d{2}-\d{2}-\d{4}$/', $query['DD']);
	}

	/**
	 * The 3.x client stored an AppId but never sent it.
	 */
	public function test_it_sends_the_app_id_when_one_is_configured()
	{
		$this->mockResponse(200, self::RESPONSE);

		$this->client()->servicePoints()->nearest('1020')->get();

		$this->assertSame('A001', $this->sentQuery()['AppId']);
	}

	public function test_it_sends_the_mandatory_api_key_header()
	{
		$this->mockResponse(200, self::RESPONSE);

		$this->client()->servicePoints()->nearest('1020')->get();

		$this->assertSame('test-api-key', $this->lastRequest()->getHeaderLine('x-api-key'));
		$this->assertSame('gzip', $this->lastRequest()->getHeaderLine('Accept-Encoding'));
	}

	public function test_it_combines_point_types_into_a_bitmask()
	{
		$this->mockResponse(200, self::RESPONSE);

		$this->client()->servicePoints()->nearest('1020')
			->types(PointType::PostOffice, PointType::PostPoint, PointType::ParcelPoint)
			->get();

		$this->assertSame('19', $this->sentQuery()['Type']);
	}

	public function test_it_narrows_the_search()
	{
		$this->mockResponse(200, self::RESPONSE);

		$this->client()->servicePoints()->nearest('59800')
			->country('fr')
			->language(Language::FR)
			->limit(20)
			->deliveryDate(new DateTimeImmutable('2026-02-19'))
			->withDetails()
			->withHolidays()
			->withBoxNumber()
			->withAttributes()
			->get();

		$query = $this->sentQuery();

		$this->assertSame('FR', $query['Country']);
		$this->assertSame('FR', $query['Language']);
		$this->assertSame('20', $query['Limit']);
		$this->assertSame('19-02-2026', $query['DD']);
		$this->assertSame('1', $query['Info']);
		$this->assertSame('1', $query['CheckList']);
		$this->assertSame('1', $query['IncludeBoxNumber']);
		$this->assertSame('1', $query['IncludeAttributes']);
	}

	public function test_it_repeats_attribute_filters()
	{
		$this->mockResponse(200, self::RESPONSE);

		$this->client()->servicePoints()->nearest('1020')
			->filterNightDelivery()
			->filterLockerType(LockerType::LeanLocker)
			->get();

		$this->assertSame(
			['NIGHTDELIVERY:TRUE', 'LOCKERTYPE:LEANLOCKER'],
			$this->sentQuery()['AttributeFilter'],
		);
	}

	public function test_it_maps_the_response_to_service_points()
	{
		$this->mockResponse(200, self::RESPONSE);

		$points = $this->client()->servicePoints()->nearest('1020')->get();

		$this->assertCount(1, $points);

		$point = $points[0];
		$this->assertSame('42599', $point->id);
		$this->assertSame(PointType::ParcelLocker, $point->type);
		$this->assertSame('BE', $point->country);
		$this->assertSame('b', $point->boxNumber);
		$this->assertSame(547.81, $point->distance);
		$this->assertSame(LockerType::Classic, $point->attributes->lockerType);
		$this->assertFalse($point->attributes->nightDelivery);
		$this->assertSame('00:01', $point->openingHours->for(\Webatvantage\Bpost\Api\Enums\Weekday::Monday)->amOpen);
	}

	/**
	 * A search answers <Info ServiceRef>, which is the XML webservice rather than a page.
	 */
	public function test_the_page_url_is_a_page_and_not_the_info_webservice()
	{
		$this->mockResponse(200, self::RESPONSE);

		$point = $this->client()->servicePoints()->nearest('1020')->get()[0];

		$this->assertStringContainsString('Function=page', (string)$point->pageUrl);
		$this->assertStringNotContainsString('Function=info', (string)$point->pageUrl);
		$this->assertStringContainsString('Id=42599', (string)$point->pageUrl);
		$this->assertStringContainsString('Partner=999999', (string)$point->pageUrl);
	}

	/**
	 * The Geolocator answers HTTP 200 and reports failure inside the document.
	 */
	public function test_it_raises_a_locator_error_reported_in_the_body()
	{
		$this->mockResponse(200, <<<'XML'
			<TaxipostLocator version="1.0" type="TaxipostLocatorError">
				<txt>Unknown partner</txt>
				<status>401</status>
			</TaxipostLocator>
			XML);

		$this->expectException(LocatorException::class);
		$this->expectExceptionMessage('Unknown partner');

		$this->client()->servicePoints()->nearest('1020')->get();
	}
}
