<?php

namespace Webatvantage\Bpost\Api\Tests\Parcel\Requests;

use Webatvantage\Bpost\Api\Exceptions\InvalidResponseException;
use Webatvantage\Bpost\Api\Tests\Parcel\ParcelTestCase;

class TrackingTest extends ParcelTestCase
{
	private function track()
	{
		$this->mockResponse(200, $this->fixture('tracking-info.xml'));

		return $this->client()->tracking()->get('323212345659900040669030');
	}

	public function test_it_gets_the_documented_endpoint()
	{
		$this->track();

		$request = $this->lastRequest();

		$this->assertSame('GET', $request->getMethod());
		$this->assertSame(
			'/services/trackedmail/item/323212345659900040669030/trackingInfo',
			$request->getUri()->getPath(),
		);
		$this->assertSame('Basic ' . base64_encode('123456:secret'), $request->getHeaderLine('Authorization'));
	}

	public function test_it_reads_the_parcel_and_its_parties()
	{
		$tracking = $this->track();

		$this->assertSame('323212345659900040669030', $tracking->itemCode);
		$this->assertSame('TEST COMPANY NAME', $tracking->sender?->name);
		$this->assertSame('WETSTRAAT', $tracking->sender?->address?->streetName);
		$this->assertSame('BARCODE TEAM BPACK', $tracking->addressee?->name);
		$this->assertSame('BARCODESPARCELS@POST.BE', $tracking->addressee?->contactDetail?->emailAddress);
	}

	/**
	 * bpost lower-cases the d in departure but not in destination.
	 */
	public function test_it_reads_both_spellings_of_the_route()
	{
		$tracking = $this->track();

		$this->assertSame('BRUSSELS', $tracking->cityOrCountryOfDeparture);
		$this->assertSame('BRUSSELS', $tracking->cityOrCountryOfDestination);
		$this->assertSame('BARCODE TEAM BPACK', $tracking->nameOfDestination);
	}

	public function test_it_reads_the_scan_history_in_order()
	{
		$tracking = $this->track();

		$this->assertCount(3, $tracking->states);
		$this->assertSame('T00', $tracking->states[0]->stateCode);
		$this->assertSame('U01', $tracking->latestState()?->stateCode);
		$this->assertSame('DistributedNormally - regular', $tracking->latestState()?->stateDescription);
		$this->assertSame('2012-11-23', $tracking->latestState()?->time?->format('Y-m-d'));
	}

	public function test_it_knows_the_parcel_was_delivered()
	{
		$tracking = $this->track();

		$this->assertTrue($tracking->isDelivered());
		$this->assertSame('2012-11-23T09:31:07+01:00', $tracking->deliveryTime?->format('c'));
	}

	public function test_it_reads_the_parcel_details()
	{
		$tracking = $this->track();

		$this->assertSame(380, $tracking->itemDetail?->weightInGrams);
		$this->assertSame('01', $tracking->itemDetail?->type);
		$this->assertSame('TEST AUTOMATIC SORTER', $tracking->customerReference);
	}

	public function test_it_builds_the_customer_facing_tracking_url()
	{
		$tracking = $this->track();

		$this->assertSame('gqwxvsyt', $tracking->trackingId);
		$this->assertSame('https://track.bpost.be/id/gqwxvsyt', $tracking->trackingUrl());
	}

	public function test_it_reads_where_the_parcel_is_waiting()
	{
		$tracking = $this->track();

		$this->assertSame('805140', $tracking->pickupPoint?->id);
		$this->assertSame('LIBRAIRIE WILSON', $tracking->pickupPoint?->name);
		$this->assertSame('1050', $tracking->pickupPoint?->postalCode);
	}

	public function test_an_unknown_barcode_surfaces_the_api_error()
	{
		$this->mockResponse(404, 'No item found for this barcode', ['Content-Type' => 'text/plain']);

		$this->expectException(InvalidResponseException::class);
		$this->expectExceptionMessage('No item found for this barcode');

		$this->client()->tracking()->get('000000000000000000000000');
	}
}
