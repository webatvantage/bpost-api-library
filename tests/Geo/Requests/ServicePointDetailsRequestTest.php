<?php

namespace Webatvantage\Bpost\Api\Tests\Geo\Requests;

use Webatvantage\Bpost\Api\Geo\Enums\PointType;
use Webatvantage\Bpost\Api\Geo\Exceptions\LocatorException;
use Webatvantage\Bpost\Api\Tests\Geo\GeoTestCase;

class ServicePointDetailsRequestTest extends GeoTestCase
{
	/**
	 * A details lookup answers the uppercase spellings, unlike a nearest-points search.
	 */
	private const RESPONSE = <<<'XML'
		<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
		<TaxipostLocator version="1.0" type="TaxipostLocatorInfo">
			<Poi>
				<Record>
					<ID>643168</ID>
					<Type>2</Type>
					<OFFICE>CARREFOUR EXPRESS</OFFICE>
					<STREET>RUE PIERRE MAUROY</STREET>
					<NR>137</NR>
					<ZIP>59800</ZIP>
					<CITY>LILLE</CITY>
					<Country>FR</Country>
					<Note>Closed for refurbishment</Note>
					<Services>
						<Service category="parcel" flag="Y">bpack@bpost</Service>
					</Services>
				</Record>
				<Page ServiceRef="https://pudo.bpost.cloud/Locator?Function=page&amp;Id=643168"/>
			</Poi>
		</TaxipostLocator>
		XML;

	public function test_it_sends_the_info_function_with_the_id_and_type()
	{
		$this->mockResponse(200, self::RESPONSE);

		$this->client()->servicePoints()->details('643168', PointType::PostPoint)->get();

		$query = $this->sentQuery();

		$this->assertSame('info', $query['Function']);
		$this->assertSame('643168', $query['Id']);
		$this->assertSame('2', $query['Type']);
		$this->assertSame('999999', $query['Partner']);
	}

	public function test_it_reads_the_uppercase_element_spellings()
	{
		$this->mockResponse(200, self::RESPONSE);

		$point = $this->client()->servicePoints()->details('643168', PointType::PostPoint)->get();

		$this->assertSame('643168', $point->id);
		$this->assertSame('CARREFOUR EXPRESS', $point->name);
		$this->assertSame('RUE PIERRE MAUROY', $point->street);
		$this->assertSame('137', $point->number);
		$this->assertSame('59800', $point->zip);
		$this->assertSame('LILLE', $point->city);
		$this->assertSame('FR', $point->country);
	}

	/**
	 * A search answers <Note>, a details lookup <NOTE>. 3.x only read the uppercase one.
	 */
	public function test_it_reads_the_note_in_either_spelling()
	{
		$this->mockResponse(200, self::RESPONSE);

		$point = $this->client()->servicePoints()->details('643168', PointType::PostPoint)->get();

		$this->assertSame('Closed for refurbishment', $point->note);
	}

	public function test_it_reads_services_and_the_details_page()
	{
		$this->mockResponse(200, self::RESPONSE);

		$point = $this->client()->servicePoints()->details('643168', PointType::PostPoint)->get();

		$this->assertCount(1, $point->services);
		$this->assertSame('bpack@bpost', $point->services[0]->name);
		$this->assertSame('parcel', $point->services[0]->category);
		$this->assertStringContainsString('Function=page', (string)$point->pageUrl);
	}

	public function test_it_raises_when_no_point_is_returned()
	{
		$this->mockResponse(200, '<TaxipostLocator type="TaxipostLocatorInfo"></TaxipostLocator>');

		$this->expectException(LocatorException::class);

		$this->client()->servicePoints()->details('000000', PointType::PostPoint)->get();
	}
}
