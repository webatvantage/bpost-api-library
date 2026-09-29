<?php

namespace Webatvantage\Bpost\Api\Tests\Shm\DataObjects\Box;

use Webatvantage\Bpost\Api\DataObjects\OpeningHours;
use Webatvantage\Bpost\Api\Enums\Language;
use Webatvantage\Bpost\Api\Enums\Weekday;
use Webatvantage\Bpost\Api\Exceptions\InvalidValueException;
use Webatvantage\Bpost\Api\Exceptions\UnexpectedValueException;
use Webatvantage\Bpost\Api\Shm\DataObjects\Box\At247;
use Webatvantage\Bpost\Api\Shm\DataObjects\Box\AtBpost;
use Webatvantage\Bpost\Api\Shm\DataObjects\Box\AtHome;
use Webatvantage\Bpost\Api\Shm\DataObjects\Box\AtIntlPugo;
use Webatvantage\Bpost\Api\Shm\DataObjects\Box\Dimensions;
use Webatvantage\Bpost\Api\Shm\DataObjects\Box\International;
use Webatvantage\Bpost\Api\Shm\DataObjects\Box\Unregistered;
use Webatvantage\Bpost\Api\Shm\DataObjects\Customs\CustomsInfo;
use Webatvantage\Bpost\Api\Shm\DataObjects\Customs\ParcelContent;
use Webatvantage\Bpost\Api\Shm\DataObjects\Options\CashOnDelivery;
use Webatvantage\Bpost\Api\Shm\DataObjects\Options\Flags\Fragile;
use Webatvantage\Bpost\Api\Shm\DataObjects\ParcelsDepotAddress;
use Webatvantage\Bpost\Api\Shm\DataObjects\PugoAddress;
use Webatvantage\Bpost\Api\Shm\DataObjects\Receiver;
use Webatvantage\Bpost\Api\Shm\Enums\ParcelReturnInstruction;
use Webatvantage\Bpost\Api\Shm\Enums\Product;
use Webatvantage\Bpost\Api\Shm\Enums\ShipmentType;
use Webatvantage\Bpost\Api\Tests\Shm\ShmTestCase;

class BoxTest extends ShmTestCase
{
	/**
	 * 3.x wrote <parcelLockerReducedMobilityZone/>, which bpost does not recognise. Its own v5
	 * example uses <reducedMobilityZone/>.
	 */
	public function test_a_locker_box_writes_the_reduced_mobility_flag_bpost_recognises()
	{
		$box = new At247()->weight(2000)
			->parcelsDepot('014472', 'WIJNEGEM', new ParcelsDepotAddress()->streetName('Turnhoutsebaan'))
			->unregistered(
				new Unregistered()->language(Language::FR)->mobilePhone('0475123456')
					->emailAddress('receiver@mail.com')->reducedMobilityZone(),
			);

		$xml = $this->serialise($box->toXml(...));

		$this->assertXmlContains('<reducedMobilityZone/>', $xml);
		$this->assertStringNotContainsString('parcelLockerReducedMobilityZone', $xml);
		$this->assertXmlContains('<at24-7>', $xml);
	}

	/**
	 * 3.x wrote <unregistered> but never read it back, so a retrieved order lost it silently.
	 */
	public function test_a_locker_box_reads_back_its_unregistered_block()
	{
		$box = At247::fromXml($this->parse(
			'<at24-7><product>bpack 24/7</product><weight>2000</weight>'
			. '<unregistered><language>FR</language><mobilePhone>0475123456</mobilePhone>'
			. '<emailAddress>receiver@mail.com</emailAddress><reducedMobilityZone/></unregistered>'
			. '</at24-7>',
		));

		$this->assertNotNull($box->unregistered);
		$this->assertSame(Language::FR, $box->unregistered->language);
		$this->assertSame('receiver@mail.com', $box->unregistered->emailAddress);
		$this->assertTrue($box->unregistered->reducedMobilityZone);
	}

	/**
	 * 3.x could parse one of these but never build one: toXML() called two methods that do not
	 * exist, so it was an unconditional fatal error.
	 */
	public function test_an_international_pick_up_box_can_be_built()
	{
		$box = new AtIntlPugo()
			->weight(2000)
			->receiver(new Receiver()->name('name_of_final_receiver'))
			->customsInfo(
				new CustomsInfo(1000, ShipmentType::Goods, ParcelReturnInstruction::ReturnToSender)
					->contentDescription('Test description'),
			)
			->pugo('163372', 'name_of_delivery_point', new PugoAddress()->streetName('street_of_delivery_point'));

		$xml = $this->serialise($box->toXml(...));

		$this->assertXmlContains('<tns:internationalBox>', $xml);
		$this->assertXmlContains('<international:atIntlPugo>', $xml);
		$this->assertXmlContains('<international:product>bpack@bpost international</international:product>', $xml);
		$this->assertXmlContains('<international:pugoId>163372</international:pugoId>', $xml);
		$this->assertXmlContains('<pugoAddress>', $xml);
	}

	public function test_an_international_pick_up_box_reads_back_its_receiver_name()
	{
		$box = AtIntlPugo::fromXml($this->parse(
			'<atIntlPugo><product>bpack@bpost international</product><parcelWeight>2000</parcelWeight>'
			. '<receiverName>John Doe</receiverName><receiverCompany>bpost</receiverCompany></atIntlPugo>',
		));

		$this->assertSame('John Doe', $box->receiverName);
		$this->assertSame('bpost', $box->receiverCompany);
	}

	/**
	 * bpost accepted a European shipment without one against the live API, so the element is left
	 * out rather than sent empty.
	 */
	public function test_a_customs_declaration_may_omit_the_content_description()
	{
		$box = new International(Product::BpackEuropeBusiness)
			->weight(1250)
			->customsInfo(new CustomsInfo(625, ShipmentType::Documents, ParcelReturnInstruction::ReturnToSender));

		$xml = $this->serialise($box->toXml(...));

		$this->assertStringNotContainsString('contentDescription', $xml);
		$this->assertXmlContains('<international:parcelValue>625</international:parcelValue>', $xml);
	}

	public function test_an_international_box_carries_electronic_advance_data()
	{
		$box = new International(Product::BpackWorldBusiness)
			->weight(1250)
			->customsInfo(
				new CustomsInfo(625, ShipmentType::Gift, ParcelReturnInstruction::ReturnToSender)
					->contentDescription('Ipad 6'),
			)
			->withParcelContent(new ParcelContent(2, 200, 't-shirt ARMANI L WINTER 2020', 400, '61091000', 'US'));

		$xml = $this->serialise($box->toXml(...));

		$this->assertStringContainsString('<international:parcelContents>', $xml);
		$this->assertStringContainsString('<international:hsTariffCode>61091000</international:hsTariffCode>', $xml);
		$this->assertStringContainsString('<international:originOfGoods>US</international:originOfGoods>', $xml);
	}

	public function test_it_refuses_more_than_ten_parcel_contents()
	{
		$box = new International(Product::BpackWorldBusiness);
		$content = new ParcelContent(1, 100, 'thing', 10, '1234', 'BE');

		for ($i = 0; $i < 10; $i++)
		{
			$box->withParcelContent($content);
		}

		$this->expectException(InvalidValueException::class);

		$box->withParcelContent($content);
	}

	/**
	 * bpack XL was missing from 3.x, along with its dimensions and the fragile option.
	 */
	public function test_bpack_xl_carries_dimensions_and_fragile()
	{
		$box = new AtHome(Product::BpackXL)
			->withOption(new Fragile())
			->weight(100000)
			->dimensions(new Dimensions(100, 200, 500));

		$xml = $this->serialise($box->toXml(...));

		$this->assertXmlContains('<product>bpack XL</product>', $xml);
		$this->assertXmlContains('<common:fragile/>', $xml);
		$this->assertXmlContains('<weight>100000</weight>', $xml);
		$this->assertXmlContains('<height>100</height>', $xml);
		$this->assertXmlContains('<length>200</length>', $xml);
		$this->assertXmlContains('<width>500</width>', $xml);
	}

	public function test_an_unknown_customs_value_raises_a_library_exception()
	{
		$this->expectException(UnexpectedValueException::class);

		CustomsInfo::fromXml($this->parse(
			'<customsInfo><parcelValue>1000</parcelValue><contentDescription>Books</contentDescription>'
			. '<shipmentType>SOMETHING_NEW</shipmentType>'
			. '<parcelReturnInstructions>RTS</parcelReturnInstructions>'
			. '<privateAddress>false</privateAddress></customsInfo>',
		));
	}

	public function test_dimensions_are_refused_for_other_products()
	{
		$this->expectException(InvalidValueException::class);

		new AtHome(Product::Bpack24hPro)->dimensions(new Dimensions(100, 200, 500));
	}

	public function test_a_box_refuses_a_product_its_delivery_method_does_not_offer()
	{
		$this->expectException(InvalidValueException::class);

		new AtHome(Product::BpackAtBpost);
	}

	/**
	 * 3.x never checked this, so a 40 kg parcel was sent and rejected by bpost.
	 */
	public function test_it_refuses_a_parcel_over_thirty_kilos()
	{
		$this->expectException(InvalidValueException::class);

		new AtHome(Product::Bpack24hPro)->weight(30001);
	}

	public function test_a_pick_up_box_writes_opening_hours_as_bpost_spells_them()
	{
		$box = new AtBpost(Product::BpackClickAndCollect)
			->weight(2000)
			->openingHours(
				new OpeningHours()
					->on(Weekday::Monday, '10:00-12:00/13:00-17:30')
					->closed(Weekday::Wednesday),
			)
			->pugo('001', 'Mijn Winkel', new PugoAddress()->streetName('Grote Markt'))
			->shopHandlingInstruction('Leave at the counter');

		$xml = $this->serialise($box->toXml(...));

		$this->assertStringContainsString('<Monday>10:00-12:00/13:00-17:30</Monday>', $xml);
		$this->assertStringContainsString('<Wednesday>-/-</Wednesday>', $xml);
		$this->assertStringContainsString('<shopHandlingInstruction>Leave at the counter</shopHandlingInstruction>', $xml);
	}

	public function test_opening_hours_refuse_a_shape_bpost_does_not_accept()
	{
		$this->expectException(InvalidValueException::class);

		new OpeningHours()->on(Weekday::Monday, '9-5');
	}

	public function test_a_pick_up_box_carries_cash_on_delivery()
	{
		$box = new AtBpost()->weight(2000)
			->withOption(new CashOnDelivery(1251, 'BE19210023508812', 'GEBABEBB'));

		$this->assertStringContainsString('<common:codAmount>1251</common:codAmount>', $this->serialise($box->toXml(...)));
	}
}
