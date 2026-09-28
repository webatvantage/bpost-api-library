<?php

namespace Webatvantage\Bpost\Api\Tests\Shm\DataObjects;

use DateTimeImmutable;
use Webatvantage\Bpost\Api\Exceptions\InvalidLengthException;
use Webatvantage\Bpost\Api\Shm\DataObjects\Address;
use Webatvantage\Bpost\Api\Shm\DataObjects\Box;
use Webatvantage\Bpost\Api\Shm\DataObjects\Box\AtHome;
use Webatvantage\Bpost\Api\Shm\DataObjects\Option\Flags\Signed;
use Webatvantage\Bpost\Api\Shm\DataObjects\Order;
use Webatvantage\Bpost\Api\Shm\DataObjects\Receiver;
use Webatvantage\Bpost\Api\Shm\DataObjects\Sender;
use Webatvantage\Bpost\Api\Shm\Enums\Product;
use Webatvantage\Bpost\Api\Shm\Support\Xml;
use Webatvantage\Bpost\Api\Tests\Shm\ShmTestCase;

class OrderTest extends ShmTestCase
{
	/**
	 * Built against bpost's own "bpack 24h Pro VAS 036 - Signed" example, copied verbatim into
	 * tests/Fixtures. If bpost changes the document shape, this is what notices.
	 */
	public function test_it_reproduces_bposts_own_create_order_example()
	{
		$order = Order::make('bpack 24h Pro - Signed')
			->costCenter('Cost Center')
			->addLine('Product 1', 1)
			->addLine('Product 1', 5)
			->addBox(
				Box::make()
					->sender(
						Sender::make()->name('SENDER NAME')->company('SENDER COMPANY')
							->address(
								Address::make()->streetName('MUNT')->number(1)->box(1)
									->postalCode(1000)->locality('Brussel')->countryCode('BE'),
							)
							->emailAddress('sender@mail.be')->phoneNumber('022011111'),
					)
					->deliverTo(
						AtHome::make(Product::Bpack24hPro)
							->withOption(new Signed())
							->weight(500)
							->receiver(
								Receiver::make()->name('RECEIVER NAME')->company('RECEIVER COMPANY')
									->address(
										Address::make()->streetName('GROTE MARKT')->number(10)->box('A')
											->postalCode(2000)->locality('Antwerpen')->countryCode('BE'),
									)
									->emailAddress('receiver@mail.be')->phoneNumber('0032475123456'),
							)
							->requestedDeliveryDate(new DateTimeImmutable('2023-12-20')),
					)
					->remark('bpack 24h Pro VAS 036 - Signed')
					->additionalCustomerReference('Reference that can be used for cross-referencing'),
			);

		$document = Xml::document();
		$document->appendChild($order->toXml($document, '{accountID}'));

		$this->assertXmlFragment(
			$this->fixture('create-order-at-home.xml'),
			Xml::toString($document),
		);
	}

	/**
	 * 3.x appended a "+PHP8.2" suffix and wrote the element whether or not a reference was set, so
	 * every box carried one.
	 */
	public function test_it_omits_the_customer_reference_when_none_was_given()
	{
		$order = Order::make('ref')->addBox(Box::make()->deliverTo(AtHome::make(Product::Bpack24hPro)));

		$document = Xml::document();
		$document->appendChild($order->toXml($document, '123456'));
		$xml = Xml::toString($document);

		$this->assertStringNotContainsString('additionalCustomerReference', $xml);
		$this->assertStringNotContainsString('PHP', $xml);
	}

	public function test_it_rejects_an_oversized_reference_and_cost_center()
	{
		$this->expectException(InvalidLengthException::class);

		Order::make(str_repeat('a', 51));
	}

	public function test_it_rejects_an_oversized_cost_center()
	{
		$this->expectException(InvalidLengthException::class);

		Order::make('ref')->costCenter(str_repeat('a', 51));
	}

	public function test_it_rejects_an_oversized_remark()
	{
		$this->expectException(InvalidLengthException::class);

		Box::make()->remark(str_repeat('a', 51));
	}
}
