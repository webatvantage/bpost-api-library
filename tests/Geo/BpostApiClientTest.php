<?php

namespace Webatvantage\Bpost\Api\Tests\Geo;

use Webatvantage\Bpost\Api\BpostApiClient;
use Webatvantage\Bpost\Api\BpostApiConfig;
use Webatvantage\Bpost\Api\Exceptions\MissingConfigurationException;
use Webatvantage\Bpost\Api\Geo\GeoApiClient;
use Webatvantage\Bpost\Api\Geo\GeoApiConfig;
use Webatvantage\Bpost\Api\Tests\TestCase;

class BpostApiClientTest extends TestCase
{
	public function test_it_hands_out_a_geo_client()
	{
		$client = new BpostApiClient(new BpostApiConfig(
			geo: new GeoApiConfig(partner: '999999', apiKey: 'key'),
		));

		$this->assertInstanceOf(GeoApiClient::class, $client->geo());
	}

	public function test_it_explains_an_unconfigured_service_rather_than_failing_at_the_http_layer()
	{
		$client = new BpostApiClient(new BpostApiConfig());

		$this->expectException(MissingConfigurationException::class);
		$this->expectExceptionMessage('No configuration was given for the "geo" API');

		$client->geo();
	}
}
