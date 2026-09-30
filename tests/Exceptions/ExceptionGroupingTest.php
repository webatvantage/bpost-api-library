<?php

namespace Webatvantage\Bpost\Api\Tests\Exceptions;

use Webatvantage\Bpost\Api\Exceptions\BpostException;
use Webatvantage\Bpost\Api\Exceptions\InvalidArgumentException;
use Webatvantage\Bpost\Api\Exceptions\InvalidLengthException;
use Webatvantage\Bpost\Api\Exceptions\InvalidValueException;
use Webatvantage\Bpost\Api\Exceptions\UnexpectedValueException;
use Webatvantage\Bpost\Api\Shm\DataObjects\Address;
use Webatvantage\Bpost\Api\Shm\DataObjects\Box\At247;
use Webatvantage\Bpost\Api\Shm\DataObjects\Box\AtHome;
use Webatvantage\Bpost\Api\Shm\DataObjects\Box\International;
use Webatvantage\Bpost\Api\Shm\DataObjects\Customs\ParcelContent;
use Webatvantage\Bpost\Api\Shm\DataObjects\Options\OptionFactory;
use Webatvantage\Bpost\Api\Shm\Enums\Product;
use Webatvantage\Bpost\Api\Tests\TestCase;

class ExceptionGroupingTest extends TestCase
{
	public function test_a_value_the_caller_set_is_an_invalid_argument()
	{
		try
		{
			new AtHome(Product::Bpack24hPro)->weight(30001);
		}
		catch (BpostException $exception)
		{
			$this->assertInstanceOf(InvalidArgumentException::class, $exception);
			$this->assertNotInstanceOf(UnexpectedValueException::class, $exception);

			return;
		}

		$this->fail('An over-heavy parcel should not be accepted.');
	}

	public function test_a_value_bpost_sent_is_an_unexpected_value()
	{
		try
		{
			OptionFactory::fromXml($this->parse('<somethingNew/>'));
		}
		catch (BpostException $exception)
		{
			$this->assertInstanceOf(UnexpectedValueException::class, $exception);
			$this->assertNotInstanceOf(InvalidArgumentException::class, $exception);

			return;
		}

		$this->fail('An option the library does not know should not be accepted.');
	}

	/**
	 * The ceiling is a rule about what may be sent. A box bpost already holds is reported as it is.
	 */
	public function test_a_retrieved_box_is_not_judged_by_the_sending_rules()
	{
		$box = At247::fromXml($this->parse(
			'<at24-7><product>bpack 24/7</product><weight>100000</weight></at24-7>',
		));

		$this->assertSame(100000, $box->weight);
	}

	/**
	 * The same rule for the documented field lengths, which used to be applied on the way in as
	 * well: a retrieved order carrying a locality longer than the manual's 40 threw at the caller,
	 * who had nothing to correct.
	 */
	public function test_a_retrieved_address_keeps_a_field_longer_than_the_manual_documents()
	{
		$locality = str_repeat('a', 41);

		$address = Address::fromXml($this->parse("<address><locality>{$locality}</locality></address>"));

		$this->assertSame($locality, $address->locality);
	}

	public function test_setting_that_same_field_still_refuses_it()
	{
		$this->expectException(InvalidLengthException::class);

		new Address()->locality(str_repeat('a', 41));
	}

	/**
	 * The constructor checks cannot be stepped around by assigning afterwards, so these reads run
	 * inside Validate::ignoring() instead.
	 */
	public function test_a_retrieved_parcel_content_keeps_a_weight_below_the_sending_minimum()
	{
		$content = ParcelContent::fromXml($this->parse(
			'<parcelContent><numberOfItemType>0</numberOfItemType><nettoWeight>0</nettoWeight>'
			. '<originOfGoods>BELGIUM</originOfGoods></parcelContent>',
		));

		$this->assertSame(0, $content->nettoWeight);
		$this->assertSame(0, $content->numberOfItemType);
		$this->assertSame('BELGIUM', $content->originOfGoods);
	}

	public function test_building_that_same_parcel_content_still_refuses_it()
	{
		$this->expectException(InvalidValueException::class);

		new ParcelContent(0, 100, 'Socks', 0, '6115', 'BE');
	}

	/**
	 * bpost accepts between one and ten contents. An order it already holds is reported whole.
	 */
	public function test_a_retrieved_international_box_keeps_more_contents_than_may_be_sent()
	{
		$content = '<parcelContent><numberOfItemType>1</numberOfItemType><valueOfItem>100</valueOfItem>'
			. '<itemDescription>Socks</itemDescription><nettoWeight>100</nettoWeight>'
			. '<hsTariffCode>6115</hsTariffCode><originOfGoods>BE</originOfGoods></parcelContent>';

		$box = International::fromXml($this->parse(
			'<international><product>bpack World Business</product><parcelContents>'
			. str_repeat($content, International::MAX_PARCEL_CONTENTS + 2)
			. '</parcelContents></international>',
		));

		$this->assertCount(International::MAX_PARCEL_CONTENTS + 2, $box->parcelContents);
	}

	/**
	 * The suspension is scoped to the one read, so a failure part-way through cannot leave the
	 * send-side checks switched off for everything after it.
	 */
	public function test_a_failed_read_leaves_the_sending_rules_in_force()
	{
		try
		{
			// The product is unknown, so this throws from inside the ignoring scope.
			At247::fromXml($this->parse('<at24-7><product>bpack Imaginary</product></at24-7>'));
		}
		catch (UnexpectedValueException)
		{
		}

		$this->expectException(InvalidLengthException::class);

		new Address()->locality(str_repeat('a', 41));
	}
}
