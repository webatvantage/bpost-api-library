<?php

namespace Webatvantage\Bpost\Api\Tests\Shm\DataObjects\Option;

use Webatvantage\Bpost\Api\Enums\Language;
use Webatvantage\Bpost\Api\Exceptions\InvalidValueException;
use Webatvantage\Bpost\Api\Shm\DataObjects\Option\AutomaticSecondPresentation;
use Webatvantage\Bpost\Api\Shm\DataObjects\Option\CashOnDelivery;
use Webatvantage\Bpost\Api\Shm\DataObjects\Option\Fragile;
use Webatvantage\Bpost\Api\Shm\DataObjects\Option\Insured;
use Webatvantage\Bpost\Api\Shm\DataObjects\Option\Messaging;
use Webatvantage\Bpost\Api\Shm\DataObjects\Option\OptionFactory;
use Webatvantage\Bpost\Api\Shm\DataObjects\Option\SaturdayDelivery;
use Webatvantage\Bpost\Api\Shm\DataObjects\Option\Signed;
use Webatvantage\Bpost\Api\Shm\Enums\InsuranceAmount;
use Webatvantage\Bpost\Api\Tests\Shm\ShmTestCase;

class OptionTest extends ShmTestCase
{
	public function test_flag_options_are_empty_elements()
	{
		$this->assertXmlFragment('<common:signed/>', $this->serialise(new Signed()->toXml(...)));
		$this->assertXmlFragment('<common:saturdayDelivery/>', $this->serialise(new SaturdayDelivery()->toXml(...)));
		$this->assertXmlFragment('<common:fragile/>', $this->serialise(new Fragile()->toXml(...)));
		$this->assertXmlFragment(
			'<common:automaticSecondPresentation/>',
			$this->serialise(new AutomaticSecondPresentation()->toXml(...)),
		);
	}

	public function test_messaging_carries_the_language_as_an_attribute()
	{
		$this->assertXmlFragment(
			'<common:infoNextDay language="EN"><common:emailAddress>tester@test.com</common:emailAddress></common:infoNextDay>',
			$this->serialise(Messaging::infoNextDay(Language::EN)->email('tester@test.com')->toXml(...)),
		);

		$this->assertXmlFragment(
			'<common:keepMeInformed language="DE"><common:mobilePhone>04895121516</common:mobilePhone></common:keepMeInformed>',
			$this->serialise(Messaging::keepMeInformed(Language::DE)->sms('04895121516')->toXml(...)),
		);
	}

	/**
	 * bpost allows email or SMS per notification, never both.
	 */
	public function test_a_message_cannot_use_two_channels()
	{
		$this->expectException(InvalidValueException::class);

		Messaging::infoDistributed()->email('a@b.com')->sms('0470000000');
	}

	public function test_cash_on_delivery_is_written_in_euro_cents()
	{
		$this->assertXmlFragment(
			'<common:cod><common:codAmount>1251</common:codAmount>'
			. '<common:iban>BE19210023508812</common:iban><common:bic>GEBABEBB</common:bic></common:cod>',
			$this->serialise(new CashOnDelivery(1251, 'BE19210023508812', 'GEBABEBB')->toXml(...)),
		);
	}

	public function test_warranty_writes_the_band_as_an_attribute()
	{
		$this->assertXmlFragment(
			'<common:insured><common:basicInsurance/></common:insured>',
			$this->serialise(Insured::basic()->toXml(...)),
		);

		$this->assertXmlFragment(
			'<common:insured><common:additionalInsurance value="3"/></common:insured>',
			$this->serialise(Insured::additional(InsuranceAmount::UpTo5000)->toXml(...)),
		);
	}

	public function test_the_factory_recognises_every_option()
	{
		$options = [
			'<signed/>' => Signed::class,
			'<saturdayDelivery/>' => SaturdayDelivery::class,
			'<fragile/>' => Fragile::class,
			'<automaticSecondPresentation/>' => AutomaticSecondPresentation::class,
			'<insured><basicInsurance/></insured>' => Insured::class,
			'<cod><codAmount>1251</codAmount><iban>BE1</iban><bic>GEB</bic></cod>' => CashOnDelivery::class,
			'<infoReminder language="FR"/>' => Messaging::class,
		];

		foreach ($options as $xml => $expected)
		{
			$this->assertInstanceOf($expected, OptionFactory::fromXml(simplexml_load_string($xml)));
		}
	}

	/**
	 * 3.x had two copies of this dispatch and the international one lacked the cod case, so an
	 * international box carrying cash on delivery threw instead of parsing.
	 */
	public function test_the_factory_handles_cash_on_delivery_for_every_box_type()
	{
		$cod = OptionFactory::fromXml(
			simplexml_load_string('<cod><codAmount>500</codAmount><iban>BE19</iban><bic>GEBABEBB</bic></cod>'),
		);

		$this->assertInstanceOf(CashOnDelivery::class, $cod);
		$this->assertSame(500, $cod->amount);
	}

	public function test_the_factory_names_what_it_does_not_recognise()
	{
		$this->expectException(InvalidValueException::class);

		OptionFactory::fromXml(simplexml_load_string('<somethingNew/>'));
	}
}
