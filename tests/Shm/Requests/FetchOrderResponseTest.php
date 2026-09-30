<?php

namespace Webatvantage\Bpost\Api\Tests\Shm\Requests;

use Webatvantage\Bpost\Api\Enums\Language;
use Webatvantage\Bpost\Api\Enums\Weekday;
use Webatvantage\Bpost\Api\Shm\DataObjects\Box\AtHome;
use Webatvantage\Bpost\Api\Shm\DataObjects\Box\International;
use Webatvantage\Bpost\Api\Shm\DataObjects\Options\CashOnDelivery;
use Webatvantage\Bpost\Api\Shm\DataObjects\Options\Flags\AutomaticSecondPresentation;
use Webatvantage\Bpost\Api\Shm\DataObjects\Options\Flags\SaturdayDelivery;
use Webatvantage\Bpost\Api\Shm\DataObjects\Options\Flags\Signed;
use Webatvantage\Bpost\Api\Shm\DataObjects\Options\Insured;
use Webatvantage\Bpost\Api\Shm\DataObjects\Options\Messaging;
use Webatvantage\Bpost\Api\Shm\Enums\BoxStatus;
use Webatvantage\Bpost\Api\Shm\Enums\InsuranceAmount;
use Webatvantage\Bpost\Api\Shm\Enums\InsuranceType;
use Webatvantage\Bpost\Api\Shm\Enums\MessagingType;
use Webatvantage\Bpost\Api\Shm\Enums\Product;
use Webatvantage\Bpost\Api\Shm\ShmApiClient;
use Webatvantage\Bpost\Api\Tests\Shm\ShmTestCase;

/**
 * Reads Retrieve Order Information responses end to end.
 *
 * These are the only tests that exercise deserialisation against whole documents rather than
 * hand-written fragments, so they are what notice if the response shape drifts.
 */
class FetchOrderResponseTest extends ShmTestCase
{
	private function fetch()
	{
		$this->mockResponse(200, $this->fixture('retrieve-order.xml'));

		return new ShmApiClient($this->config(), ['handler' => $this->handlerStack()])
			->orders()
			->get('bpack 24h B2B - Ins(El)+iR+iND+iD');
	}

	private function fetchDeliveredAbroad()
	{
		$this->mockResponse(200, $this->fixture('retrieve-order-at-intl-home.xml'));

		return new ShmApiClient($this->config(), ['handler' => $this->handlerStack()])
			->orders()
			->get('202600007_2605211404');
	}

	public function test_it_reads_the_order_envelope()
	{
		$order = $this->fetch();

		$this->assertSame('bpack 24h B2B - Ins(El)+iR+iND+iD', $order->reference);
		$this->assertSame('Cost Center', $order->costCenter);
		$this->assertCount(2, $order->lines);
		$this->assertSame('Product 1', $order->lines[0]->text);
		$this->assertSame(5, $order->lines[0]->numberOfItems);
		$this->assertCount(2, $order->boxes);
	}

	public function test_it_reads_the_sender_across_the_namespace_boundary()
	{
		$sender = $this->fetch()->boxes[0]->sender;

		$this->assertNotNull($sender);
		$this->assertSame('SENDER NAME', $sender->name);
		$this->assertSame('SENDER COMPANY', $sender->company);
		$this->assertSame('MUNT', $sender->address?->streetName);
		$this->assertSame('Brussel', $sender->address?->locality);
		$this->assertSame('sender@mail.be', $sender->emailAddress);
	}

	public function test_it_reads_the_delivery_method_and_its_status()
	{
		$box = $this->fetch()->boxes[0];

		$this->assertSame(BoxStatus::Pending, $box->status);
		$this->assertInstanceOf(AtHome::class, $box->deliveryBox);
		$this->assertSame(Product::Bpack24hBusiness, $box->deliveryBox->product);
		$this->assertSame(2000, $box->deliveryBox->weight);
		$this->assertSame('RECEIVER NAME', $box->deliveryBox->receiver?->name);
	}

	public function test_it_reads_every_option_on_the_box()
	{
		$options = $this->fetch()->boxes[0]->deliveryBox->options;

		$byClass = [];

		foreach ($options as $option)
		{
			$byClass[$option::class][] = $option;
		}

		$this->assertCount(3, $byClass[Messaging::class]);
		$this->assertCount(1, $byClass[AutomaticSecondPresentation::class]);
		$this->assertCount(1, $byClass[Insured::class]);
		$this->assertCount(1, $byClass[Signed::class]);

		$this->assertSame(InsuranceAmount::UpTo2500, $byClass[Insured::class][0]->amount);
	}

	public function test_it_reads_the_messaging_channels_and_languages()
	{
		$messages = array_values(array_filter(
			$this->fetch()->boxes[0]->deliveryBox->options,
			fn ($option) => $option instanceof Messaging,
		));

		$this->assertSame(MessagingType::InfoDistributed, $messages[0]->type);
		$this->assertSame(Language::EN, $messages[0]->language);
		$this->assertSame('0476123456', $messages[0]->mobilePhone);

		$this->assertSame(MessagingType::InfoNextDay, $messages[1]->type);
		$this->assertSame('receiver@mail.be', $messages[1]->emailAddress);
	}

	public function test_it_reads_the_business_opening_hours()
	{
		$hours = $this->fetch()->boxes[0]->deliveryBox->openingHours;

		$this->assertNotNull($hours);
		$this->assertSame('10:00-12:00/13:00-17:30', $hours->for(Weekday::Monday));
		$this->assertSame('-/-', $hours->for(Weekday::Wednesday));
		$this->assertSame('08:00-17:30', $hours->for(Weekday::Thursday));
	}

	public function test_the_second_box_adds_saturday_delivery()
	{
		$options = $this->fetch()->boxes[1]->deliveryBox->options;

		$saturday = array_filter($options, fn ($option) => $option instanceof SaturdayDelivery);

		$this->assertCount(1, $saturday);
	}

	/**
	 * bpost answers an order delivered abroad in the v3 namespaces and under a name it does not
	 * accept on the way in, so nothing about this document matches what the manual documents.
	 */
	public function test_it_reads_an_order_delivered_abroad()
	{
		$box = $this->fetchDeliveredAbroad()->boxes[0];

		$this->assertSame('CD121033378BE', $box->barcode);
		$this->assertSame(BoxStatus::Announced, $box->status);
		$this->assertInstanceOf(International::class, $box->deliveryBox);
		$this->assertSame(Product::BpackWorldBusiness, $box->deliveryBox->product);
		$this->assertSame(2000, $box->deliveryBox->weight);
		$this->assertSame('RECEIVER NAME', $box->deliveryBox->receiver?->name);
		$this->assertSame('DE', $box->deliveryBox->receiver?->address?->countryCode);
	}

	public function test_it_reads_the_options_of_an_order_delivered_abroad()
	{
		$options = $this->fetchDeliveredAbroad()->boxes[0]->deliveryBox->options;

		$this->assertCount(2, $options);
		$this->assertInstanceOf(Insured::class, $options[0]);
		$this->assertSame(InsuranceType::Basic, $options[0]->type);
		$this->assertInstanceOf(Signed::class, $options[1]);
	}

	public function test_no_cash_on_delivery_is_invented()
	{
		$options = $this->fetch()->boxes[0]->deliveryBox->options;

		$this->assertCount(0, array_filter($options, fn ($o) => $o instanceof CashOnDelivery));
	}
}
