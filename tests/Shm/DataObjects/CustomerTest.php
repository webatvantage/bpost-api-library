<?php

namespace Webatvantage\Bpost\Api\Tests\Shm\DataObjects;

use Webatvantage\Bpost\Api\Exceptions\InvalidLengthException;
use Webatvantage\Bpost\Api\Shm\DataObjects\Address;
use Webatvantage\Bpost\Api\Shm\DataObjects\Receiver;
use Webatvantage\Bpost\Api\Shm\DataObjects\Sender;
use Webatvantage\Bpost\Api\Shm\Support\Xml;
use Webatvantage\Bpost\Api\Tests\Shm\ShmTestCase;

class CustomerTest extends ShmTestCase
{
	/**
	 * Matches the sender block of bpost's own v5 Create Order examples exactly.
	 */
	public function test_it_writes_the_sender_block_bpost_documents()
	{
		$sender = Sender::make()
			->name('SENDER NAME')
			->company('SENDER COMPANY')
			->address(
				Address::make()->streetName('MUNT')->number(1)->box(1)
					->postalCode(1000)->locality('Brussel')->countryCode('BE'),
			)
			->emailAddress('sender@mail.be')
			->phoneNumber('022011111');

		$this->assertXmlFragment(
			'<tns:sender>'
			. '<common:name>SENDER NAME</common:name>'
			. '<common:company>SENDER COMPANY</common:company>'
			. '<common:address>'
			. '<common:streetName>MUNT</common:streetName>'
			. '<common:number>1</common:number>'
			. '<common:box>1</common:box>'
			. '<common:postalCode>1000</common:postalCode>'
			. '<common:locality>Brussel</common:locality>'
			. '<common:countryCode>BE</common:countryCode>'
			. '</common:address>'
			. '<common:emailAddress>sender@mail.be</common:emailAddress>'
			. '<common:phoneNumber>022011111</common:phoneNumber>'
			. '</tns:sender>',
			$this->serialise(fn ($document) => $sender->toXml($document, Xml::PREFIX_GLOBAL)),
		);
	}

	public function test_a_receiver_is_the_same_block_under_another_name()
	{
		$xml = $this->serialise(Receiver::make()->name('RECEIVER NAME')->toXml(...));

		$this->assertStringContainsString('<receiver>', $xml);
		$this->assertStringContainsString('<common:name>RECEIVER NAME</common:name>', $xml);
	}

	/**
	 * 3.x validated the email and phone but left name and company unchecked, though the manual
	 * caps both at 40.
	 */
	public function test_it_rejects_a_name_longer_than_the_label_can_print()
	{
		$this->expectException(InvalidLengthException::class);

		Sender::make()->name(str_repeat('a', 41));
	}

	public function test_it_rejects_an_oversized_company()
	{
		$this->expectException(InvalidLengthException::class);

		Sender::make()->company(str_repeat('a', 41));
	}

	public function test_it_allows_a_fifty_character_email()
	{
		$email = str_repeat('a', 38) . '@example.com';

		$this->assertSame(50, mb_strlen($email));
		$this->assertSame($email, Sender::make()->emailAddress($email)->emailAddress);
	}

	public function test_it_round_trips_through_xml()
	{
		$xml = simplexml_load_string(
			'<sender><name>SENDER NAME</name><company>SENDER COMPANY</company>'
			. '<address><streetName>MUNT</streetName><countryCode>BE</countryCode></address>'
			. '<emailAddress>sender@mail.be</emailAddress><phoneNumber>022011111</phoneNumber></sender>',
		);

		$sender = Sender::fromXml($xml);

		$this->assertSame('SENDER NAME', $sender->name);
		$this->assertSame('SENDER COMPANY', $sender->company);
		$this->assertSame('MUNT', $sender->address?->streetName);
		$this->assertSame('sender@mail.be', $sender->emailAddress);
		$this->assertSame('022011111', $sender->phoneNumber);
	}
}
