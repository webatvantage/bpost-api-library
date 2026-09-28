<?php

namespace Webatvantage\Bpost\Api\Tests\ConnectionTests;

use PHPUnit\Framework\TestCase;
use Webatvantage\Bpost\Api\Parcel\DataObjects\ItemTracking;
use Webatvantage\Bpost\Api\Parcel\ParcelApiClient;
use Webatvantage\Bpost\Api\Parcel\ParcelApiConfig;

/**
 * Hits the real trackedmail service. Excluded from the default suite; run it with
 *
 *     BPOST_PARCEL_ACCOUNT=... BPOST_PARCEL_PASSWORD=... BPOST_PARCEL_BARCODE=... \
 *         vendor/bin/phpunit tests/connection-tests
 *
 * These credentials are issued separately from the Shipping Manager passphrase.
 *
 * Only tracking is exercised. Announcing a parcel is a write against bpost's production systems
 * for a barcode that has to genuinely exist, so it is not something a test suite should do on its
 * own initiative.
 */
class ParcelApiClientTest extends TestCase
{
	private ParcelApiClient $parcel;

	private string $barcode;

	protected function setUp(): void
	{
		parent::setUp();

		$account = getenv('BPOST_PARCEL_ACCOUNT');
		$password = getenv('BPOST_PARCEL_PASSWORD');
		$barcode = getenv('BPOST_PARCEL_BARCODE');

		if ($account === false || $password === false || $barcode === false)
		{
			$this->markTestSkipped(
				'Set BPOST_PARCEL_ACCOUNT, BPOST_PARCEL_PASSWORD and BPOST_PARCEL_BARCODE to run the parcel connection tests.',
			);
		}

		$this->barcode = $barcode;
		$this->parcel = new ParcelApiClient(new ParcelApiConfig(accountId: $account, password: $password));
	}

	public function test_it_tracks_a_real_parcel()
	{
		$tracking = $this->parcel->tracking()->get($this->barcode);

		$this->assertInstanceOf(ItemTracking::class, $tracking);
		$this->assertSame($this->barcode, $tracking->itemCode);
		$this->assertNotEmpty($tracking->states);
	}
}
