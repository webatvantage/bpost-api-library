<?php

namespace Webatvantage\Bpost\Api\Tests;

use Webatvantage\Bpost\Api\BpostApiClient;
use Webatvantage\Bpost\Api\BpostApiConfig;
use Webatvantage\Bpost\Api\Exceptions\MissingConfigurationException;
use Webatvantage\Bpost\Api\Geo\GeoApiConfig;
use Webatvantage\Bpost\Api\Parcel\ParcelApiConfig;
use Webatvantage\Bpost\Api\Shm\ShmApiConfig;

class BpostApiClientTest extends TestCase
{
	private function client(): BpostApiClient
	{
		return new BpostApiClient(new BpostApiConfig(
			shm: new ShmApiConfig(accountId: '123456', passphrase: 'passphrase'),
			geo: new GeoApiConfig(partner: '999999', apiKey: 'key'),
			parcel: new ParcelApiConfig(accountId: '123456', password: 'password'),
		));
	}

	private function shmOnlyClient(): BpostApiClient
	{
		return new BpostApiClient(
			new BpostApiConfig(shm: new ShmApiConfig(accountId: '123456', passphrase: 'passphrase')),
			['handler' => $this->handlerStack()],
		);
	}

	public function test_each_service_is_built_once_and_handed_back_again()
	{
		$client = $this->client();

		$this->assertSame($client->shm(), $client->shm());
		$this->assertSame($client->geo(), $client->geo());
		$this->assertSame($client->parcel(), $client->parcel());
	}

	/**
	 * Each sub-client owns a Guzzle client, so handing out a new one per call threw away the
	 * connection pool and any debug callback already set on it.
	 */
	public function test_a_debug_callback_on_a_service_survives_a_second_reach_for_it()
	{
		$client = $this->shmOnlyClient();

		$seen = 0;
		$client->shm()->withDebug(function () use (&$seen) { $seen++; });

		$this->mockResponse(200, '<orderInfo><reference>ref-1</reference></orderInfo>');
		$client->shm()->orders()->get('ref-1');

		$this->assertSame(1, $seen);
	}

	/**
	 * A resource is built fresh per call, so its callback goes no further than the calls made on
	 * it — the same reach withoutLogging() has. It used to be set on the shared adapter instead,
	 * where it stayed for the life of the client and fired for every later call on every resource.
	 */
	public function test_a_debug_callback_on_a_resource_reaches_only_the_calls_made_on_it()
	{
		$client = $this->shmOnlyClient();
		$order = '<orderInfo><reference>ref-1</reference></orderInfo>';

		$seen = 0;
		$orders = $client->shm()->orders()->withDebug(function () use (&$seen) { $seen++; });

		$this->mockResponse(200, $order);
		$orders->get('ref-1');

		$this->assertSame(1, $seen);

		$this->mockResponse(200, $order);
		$client->shm()->orders()->get('ref-1');

		$this->assertSame(1, $seen);
	}

	public function test_a_debug_callback_on_the_whole_client_reaches_a_service_built_afterwards()
	{
		$client = $this->shmOnlyClient();

		$seen = 0;
		$client->withDebug(function () use (&$seen) { $seen++; });

		$this->mockResponse(200, '<orderInfo><reference>ref-1</reference></orderInfo>');
		$client->shm()->orders()->get('ref-1');

		$this->assertSame(1, $seen);
	}

	public function test_an_unconfigured_service_still_explains_itself()
	{
		$client = new BpostApiClient(new BpostApiConfig());

		$this->expectException(MissingConfigurationException::class);
		$this->expectExceptionMessage('No configuration was given for the "shm" API');

		$client->shm();
	}
}
