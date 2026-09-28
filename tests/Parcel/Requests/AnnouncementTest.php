<?php

namespace Webatvantage\Bpost\Api\Tests\Parcel\Requests;

use Webatvantage\Bpost\Api\DataObjects\OpeningHours;
use Webatvantage\Bpost\Api\Enums\Language;
use Webatvantage\Bpost\Api\Enums\Weekday;
use Webatvantage\Bpost\Api\Exceptions\InvalidLengthException;
use Webatvantage\Bpost\Api\Exceptions\InvalidValueException;
use Webatvantage\Bpost\Api\Parcel\DataObjects\Address;
use Webatvantage\Bpost\Api\Parcel\DataObjects\Announcement;
use Webatvantage\Bpost\Api\Parcel\DataObjects\ContactDetail;
use Webatvantage\Bpost\Api\Parcel\DataObjects\Dimensions;
use Webatvantage\Bpost\Api\Parcel\DataObjects\InternationalInfo;
use Webatvantage\Bpost\Api\Parcel\DataObjects\Options\AdditionalInsurance;
use Webatvantage\Bpost\Api\Parcel\DataObjects\Options\CashOnDelivery;
use Webatvantage\Bpost\Api\Parcel\DataObjects\Options\Notification;
use Webatvantage\Bpost\Api\Parcel\DataObjects\Options\Signature;
use Webatvantage\Bpost\Api\Parcel\DataObjects\Receiver;
use Webatvantage\Bpost\Api\Parcel\DataObjects\Sender;
use Webatvantage\Bpost\Api\Parcel\Enums\DeliveryMethod;
use Webatvantage\Bpost\Api\Parcel\Enums\InsuranceAmount;
use Webatvantage\Bpost\Api\Parcel\Enums\ItemCategory;
use Webatvantage\Bpost\Api\Parcel\Enums\NonDeliveryInstruction;
use Webatvantage\Bpost\Api\Tests\Parcel\ParcelTestCase;

class AnnouncementTest extends ParcelTestCase
{
	private function sender(): Sender
	{
		return new Sender()
			->name('Sender Company Name')
			->address(
				new Address()->streetName('Sender Streetname')->houseNumber(1)
					->postalCode(1000)->city('Cityname')->countryCode('BE'),
			);
	}

	private function receiver(): Receiver
	{
		return new Receiver()
			->name('Receiver Company Name')
			->address(
				new Address()->streetName('Delivery street name')->houseNumber(2)
					->postalCode(2000)->city('city name')->countryCode('BE'),
			);
	}

	private function announcement(): Announcement
	{
		return new Announcement('323212345689100101119030', $this->sender(), $this->receiver(), 250);
	}

	public function test_it_posts_to_the_documented_endpoint()
	{
		$this->mockResponse(201, '<feedback/>');

		$this->client()->announcements()->create($this->announcement());

		$request = $this->lastRequest();

		$this->assertSame('POST', $request->getMethod());
		$this->assertSame('/services/trackedmail/announcement', $request->getUri()->getPath());
		$this->assertSame(
			'application/vnd.bpost.announcement-v1+XML;charset=UTF-8',
			$request->getHeaderLine('Content-Type'),
		);
		$this->assertSame(
			'Basic ' . base64_encode('123456:secret'),
			$request->getHeaderLine('Authorization'),
		);
	}

	/**
	 * Matches the announcement example in manual section D.2.2.1, prefixes and all.
	 */
	public function test_it_writes_the_document_bpost_documents()
	{
		$this->mockResponse(201, '<feedback/>');

		$this->client()->announcements()->create(
			$this->announcement()->productCode('030')->costCenter('cost center'),
		);

		$body = (string)$this->lastRequest()->getBody();

		$this->assertStringContainsString('<inst:announcement', $body);
		$this->assertStringContainsString('xmlns:common="http://schema.post.be/announcement/common/v1/"', $body);
		$this->assertStringContainsString('xmlns:inst="http://schema.post.be/announcement/v1/"', $body);
		$this->assertStringContainsString('<inst:accountId>123456</inst:accountId>', $body);
		$this->assertStringContainsString('<inst:type>00</inst:type>', $body);
		$this->assertStringContainsString('<inst:itemCode>323212345689100101119030</inst:itemCode>', $body);
		$this->assertStringContainsString('<common:name>Sender Company Name</common:name>', $body);
		$this->assertStringContainsString('<common:houseNumber>1</common:houseNumber>', $body);
		$this->assertStringContainsString('<inst:weightInGrams>250</inst:weightInGrams>', $body);
		$this->assertStringContainsString('<inst:deliveryMethod><common:atHome/></inst:deliveryMethod>', preg_replace('/>\s+</', '><', $body));
	}

	public function test_the_delivery_method_is_an_empty_element_inside_its_wrapper()
	{
		$this->mockResponse(201, '<feedback/>');

		$announcement = new Announcement(
			'323212345689100101119030',
			$this->sender(),
			$this->receiver(),
			250,
			DeliveryMethod::At247,
		);

		$this->client()->announcements()->create($announcement);

		$body = preg_replace('/>\s+</', '><', (string)$this->lastRequest()->getBody());

		$this->assertStringContainsString('<inst:deliveryMethod><common:at24-7/></inst:deliveryMethod>', $body);
	}

	public function test_it_writes_the_options_the_announcement_service_spells_its_own_way()
	{
		$this->mockResponse(201, '<feedback/>');

		$this->client()->announcements()->create(
			$this->announcement()
				->withOption(new Signature())
				->withOption(new AdditionalInsurance(InsuranceAmount::UpTo2500))
				->withOption(new CashOnDelivery(1251, iban: 'BE19210023508812', bic: 'GEBABEBB'))
				->withOption(Notification::infoNextDay(Language::NL)->email('a@b.be')),
		);

		$body = preg_replace('/>\s+</', '><', (string)$this->lastRequest()->getBody());

		$this->assertStringContainsString('<common:signature/>', $body);
		$this->assertStringContainsString('<common:additionalInsurance><common:maxAmount>2</common:maxAmount>', $body);
		$this->assertStringContainsString('<common:amountTotalInEuroCents>1251</common:amountTotalInEuroCents>', $body);
		$this->assertStringContainsString('<common:infoNextDay><common:language>NL</common:language>', $body);
		$this->assertStringContainsString('<common:emailAddress>a@b.be</common:emailAddress>', $body);
	}

	public function test_it_writes_the_customs_declaration_and_dimensions()
	{
		$this->mockResponse(201, '<feedback/>');

		$this->client()->announcements()->create(
			$this->announcement()
				->international(
					new InternationalInfo(
						'T-shirts',
						ItemCategory::Goods,
						NonDeliveryInstruction::ReturnToSender,
						31.23,
						'EUR',
					),
				)
				->dimensions(new Dimensions(450, 180, 1200)),
		);

		$body = preg_replace('/>\s+</', '><', (string)$this->lastRequest()->getBody());

		$this->assertStringContainsString('<common:itemCategory>GOODS</common:itemCategory>', $body);
		$this->assertStringContainsString('<common:nonDeliveryInstructions>RTS</common:nonDeliveryInstructions>', $body);
		$this->assertStringContainsString('<common:widthInMm>450</common:widthInMm>', $body);
	}

	public function test_it_writes_receiver_opening_hours_under_its_own_element_name()
	{
		$this->mockResponse(201, '<feedback/>');

		$this->client()->announcements()->create(
			$this->announcement()->receiverOpeningHours(
				new OpeningHours()->on(Weekday::Monday, '10:00-17:30')->closed(Weekday::Wednesday),
			),
		);

		$body = preg_replace('/>\s+</', '><', (string)$this->lastRequest()->getBody());

		$this->assertStringContainsString('<inst:receiverOpeningHours>', $body);
		$this->assertStringContainsString('<inst:Monday>10:00-17:30</inst:Monday>', $body);
		$this->assertStringNotContainsString('<inst:openingHours>', $body);
	}

	public function test_it_reads_warnings_and_errors_out_of_the_feedback()
	{
		$this->mockResponse(201, '<feedback><Warning>Unknown product code</Warning><Error>Weight too high</Error></feedback>');

		$feedback = $this->client()->announcements()->create($this->announcement());

		$this->assertTrue($feedback->hasWarnings());
		$this->assertTrue($feedback->hasErrors());
		$this->assertSame(['Unknown product code'], $feedback->warnings);
		$this->assertSame(['Weight too high'], $feedback->errors);
	}

	public function test_a_clean_feedback_reports_nothing()
	{
		$this->mockResponse(201, '<feedback/>');

		$feedback = $this->client()->announcements()->create($this->announcement());

		$this->assertFalse($feedback->hasWarnings());
		$this->assertFalse($feedback->hasErrors());
	}

	/**
	 * bpost converts a weight of 0 to one kilo, which is rarely what anyone meant.
	 */
	public function test_it_refuses_a_weight_outside_the_documented_range()
	{
		$this->expectException(InvalidValueException::class);

		new Announcement('323212345689100101119030', $this->sender(), $this->receiver(), 0);
	}

	public function test_it_refuses_an_oversized_item_code()
	{
		$this->expectException(InvalidLengthException::class);

		new Announcement(str_repeat('3', 31), $this->sender(), $this->receiver(), 250);
	}

	public function test_cash_on_delivery_is_bounded()
	{
		$this->expectException(InvalidValueException::class);

		new CashOnDelivery(100);
	}

	public function test_a_notification_cannot_use_two_channels()
	{
		$this->expectException(InvalidValueException::class);

		Notification::infoReminder()->email('a@b.be')->sms('0470000000');
	}

	public function test_a_contact_detail_knows_whether_it_has_a_phone_number()
	{
		$this->assertFalse(new ContactDetail()->emailAddress('a@b.be')->hasPhoneNumber());
		$this->assertTrue(new ContactDetail()->mobilePhone('0470000000')->hasPhoneNumber());
	}
}
