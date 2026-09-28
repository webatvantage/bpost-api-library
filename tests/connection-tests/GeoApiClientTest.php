<?php

namespace Webatvantage\Bpost\Api\Tests\ConnectionTests;

use PHPUnit\Framework\TestCase;
use Webatvantage\Bpost\Api\Geo\DataObjects\ServicePoint;
use Webatvantage\Bpost\Api\Geo\Enums\PointType;
use Webatvantage\Bpost\Api\Geo\Exceptions\LocatorException;
use Webatvantage\Bpost\Api\Geo\GeoApiClient;
use Webatvantage\Bpost\Api\Geo\GeoApiConfig;

/**
 * Hits the real Geolocator. Excluded from the default suite; run it with
 *
 *     BPOST_GEO_PARTNER=... BPOST_GEO_API_KEY=... vendor/bin/phpunit tests/connection-tests
 *
 * These are the two things no fixture can settle: whether the x-api-key we send is accepted, and
 * whether the page URL we build actually resolves.
 */
class GeoApiClientTest extends TestCase
{
	private GeoApiClient $geo;

	protected function setUp(): void
	{
		parent::setUp();

		$partner = getenv('BPOST_GEO_PARTNER');
		$apiKey = getenv('BPOST_GEO_API_KEY');

		if ($partner === false || $apiKey === false)
		{
			$this->markTestSkipped('Set BPOST_GEO_PARTNER and BPOST_GEO_API_KEY to run the Geolocator connection tests.');
		}

		$this->geo = new GeoApiClient(new GeoApiConfig(
			partner: $partner,
			apiKey: $apiKey,
			appId: getenv('BPOST_GEO_APP_ID') ?: null,
		));
	}

	public function test_it_finds_the_points_nearest_an_address()
	{
		$points = $this->geo->servicePoints()
			->nearest('9000', 'Afrikalaan', '289')
			->types(PointType::PostOffice, PointType::PostPoint)
			->withDetails()
			->limit(5)
			->get();

		$this->assertNotEmpty($points);
		$this->assertContainsOnlyInstancesOf(ServicePoint::class, $points);
		$this->assertNotNull($points[0]->distance);
		$this->assertGreaterThan(0, count($points[0]->openingHours));
	}

	public function test_it_reads_the_details_of_one_point()
	{
		$point = $this->geo->servicePoints()->details('220000', PointType::PostOffice)->get();

		$this->assertSame('220000', $point->id);
		$this->assertSame(PointType::PostOffice, $point->type);
	}

	public function test_it_reports_an_unknown_point()
	{
		$this->expectException(LocatorException::class);

		$this->geo->servicePoints()->details('-1', PointType::PostOffice)->get();
	}

	public function test_it_lists_every_parcel_locker_in_a_zip_code()
	{
		$points = $this->geo->servicePoints()
			->all()
			->country('BE')
			->type(PointType::ParcelLocker)
			->zip('9000')
			->get();

		$this->assertContainsOnlyInstancesOf(ServicePoint::class, $points);
	}
}
