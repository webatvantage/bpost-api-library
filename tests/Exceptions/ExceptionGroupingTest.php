<?php

namespace Webatvantage\Bpost\Api\Tests\Exceptions;

use Webatvantage\Bpost\Api\Exceptions\BpostException;
use Webatvantage\Bpost\Api\Exceptions\InvalidArgumentException;
use Webatvantage\Bpost\Api\Exceptions\UnexpectedValueException;
use Webatvantage\Bpost\Api\Shm\DataObjects\Box\At247;
use Webatvantage\Bpost\Api\Shm\DataObjects\Box\AtHome;
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
}
