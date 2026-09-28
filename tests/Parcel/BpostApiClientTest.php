<?php

namespace Webatvantage\Bpost\Api\Tests\Parcel;

use Webatvantage\Bpost\Api\BpostApiClient;
use Webatvantage\Bpost\Api\BpostApiConfig;
use Webatvantage\Bpost\Api\Exceptions\MissingConfigurationException;
use Webatvantage\Bpost\Api\Geo\GeoApiConfig;
use Webatvantage\Bpost\Api\Parcel\ParcelApiClient;
use Webatvantage\Bpost\Api\Parcel\ParcelApiConfig;
use Webatvantage\Bpost\Api\Shm\ShmApiClient;
use Webatvantage\Bpost\Api\Shm\ShmApiConfig;

class BpostApiClientTest extends ParcelTestCase
{
	public function test_it_hands_out_every_configured_service()
	{
		$client = new BpostApiClient(new BpostApiConfig(
			shm: new ShmApiConfig(accountId: '123456', passphrase: 'passphrase'),
			geo: new GeoApiConfig(partner: '123456', apiKey: 'key'),
			parcel: new ParcelApiConfig(accountId: '123456', password: 'secret'),
		));

		$this->assertInstanceOf(ShmApiClient::class, $client->shm());
		$this->assertInstanceOf(ParcelApiClient::class, $client->parcel());
	}

	public function test_it_names_the_service_that_was_not_configured()
	{
		$client = new BpostApiClient(new BpostApiConfig());

		$this->expectException(MissingConfigurationException::class);
		$this->expectExceptionMessage('No configuration was given for the "parcel" API');

		$client->parcel();
	}
}
