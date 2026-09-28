<?php

namespace Webatvantage\Bpost\Api\Tests\Shm\DataObjects;

use Webatvantage\Bpost\Api\Exceptions\InvalidLengthException;
use Webatvantage\Bpost\Api\Exceptions\InvalidValueException;
use Webatvantage\Bpost\Api\Shm\DataObjects\Address;
use Webatvantage\Bpost\Api\Shm\DataObjects\ParcelsDepotAddress;
use Webatvantage\Bpost\Api\Shm\DataObjects\PugoAddress;
use Webatvantage\Bpost\Api\Tests\Shm\ShmTestCase;

class AddressTest extends ShmTestCase
{
	public function test_it_writes_the_element_bpost_documents()
	{
		$address = Address::make()
			->streetName('Turnhoutsebaan')
			->number(468)
			->box('A')
			->postalCode(2110)
			->locality('Wijnegem')
			->countryCode('BE');

		$this->assertXmlFragment(
			'<common:address>'
			. '<common:streetName>Turnhoutsebaan</common:streetName>'
			. '<common:number>468</common:number>'
			. '<common:box>A</common:box>'
			. '<common:postalCode>2110</common:postalCode>'
			. '<common:locality>Wijnegem</common:locality>'
			. '<common:countryCode>BE</common:countryCode>'
			. '</common:address>',
			$this->serialise($address->toXml(...)),
		);
	}

	/**
	 * An empty box is not the same as an absent one; bpost's own example carries the comment
	 * "When box is empty this tag has to be removed".
	 */
	public function test_it_omits_elements_that_were_never_set()
	{
		$address = Address::make()->streetName('Muntcentrum')->number(1);

		$xml = $this->serialise($address->toXml(...));

		$this->assertStringNotContainsString('<common:box', $xml);
		$this->assertStringNotContainsString('<common:locality', $xml);
		$this->assertStringContainsString('<common:countryCode>BE</common:countryCode>', $xml);
	}

	public function test_a_pugo_address_is_named_differently()
	{
		$this->assertStringContainsString(
			'<pugoAddress>',
			$this->serialise(PugoAddress::make()->streetName('Turnhoutsebaan')->toXml(...)),
		);

		$this->assertStringContainsString(
			'<parcelsDepotAddress>',
			$this->serialise(ParcelsDepotAddress::make()->streetName('Turnhoutsebaan')->toXml(...)),
		);
	}

	public function test_it_upper_cases_the_country_code()
	{
		$this->assertSame('BE', Address::make()->countryCode('be')->countryCode);
	}

	public function test_it_rejects_a_country_code_that_is_not_two_letters()
	{
		$this->expectException(InvalidValueException::class);

		Address::make()->countryCode('BEL');
	}

	public function test_it_rejects_oversized_values()
	{
		$this->expectException(InvalidLengthException::class);

		Address::make()->streetName(str_repeat('a', 41));
	}

	/**
	 * bpost documents 8 but accepts 9, which the library has relied on since 3.7.
	 */
	public function test_it_allows_a_nine_character_box()
	{
		$this->assertSame('123456789', Address::make()->box('123456789')->box);

		$this->expectException(InvalidLengthException::class);

		Address::make()->box('1234567890');
	}

	public function test_it_round_trips_through_xml()
	{
		$xml = simplexml_load_string(
			'<address><streetName>MUNT</streetName><number>1</number><box>b</box>'
			. '<postalCode>1000</postalCode><locality>Brussel</locality><countryCode>BE</countryCode></address>',
		);

		$address = Address::fromXml($xml);

		$this->assertSame('MUNT', $address->streetName);
		$this->assertSame('1', $address->number);
		$this->assertSame('b', $address->box);
		$this->assertSame('1000', $address->postalCode);
		$this->assertSame('Brussel', $address->locality);
		$this->assertSame('BE', $address->countryCode);
	}
}
