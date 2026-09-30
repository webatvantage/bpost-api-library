<?php

namespace Webatvantage\Bpost\Api\Tests\Geo;

use Webatvantage\Bpost\Api\Geo\GeoApiClient;
use Webatvantage\Bpost\Api\Geo\GeoApiConfig;

class GeoApiKeyTest extends GeoTestCase
{
	public function test_it_sends_the_key_on_the_host_that_wants_one()
	{
		$this->mockResponse(200, '<PoiList/>');

		$this->client()->servicePoints()->nearest(zone: '1000')->get();

		$this->assertSame('test-api-key', $this->lastRequest()->getHeaderLine('x-api-key'));
	}

	/**
	 * pudo.bpost.cloud rejects a request without the key, but the older pudo.bpost.be ignores the
	 * header, so an integration still pointed there has none to send and should not be made to
	 * invent one.
	 */
	public function test_it_leaves_the_header_out_when_there_is_no_key()
	{
		$geo = new GeoApiClient(
			new GeoApiConfig(partner: '999999', baseUri: 'https://pudo.bpost.be'),
			['handler' => $this->handlerStack()],
		);

		$this->mockResponse(200, '<PoiList/>');
		$geo->servicePoints()->nearest(zone: '1000')->get();

		$request = $this->lastRequest();

		$this->assertFalse($request->hasHeader('x-api-key'));
		$this->assertSame('pudo.bpost.be', $request->getUri()->getHost());
		$this->assertSame('gzip', $request->getHeaderLine('Accept-Encoding'));
	}

	public function test_an_empty_key_is_still_sent_rather_than_guessed_at()
	{
		$geo = new GeoApiClient(
			new GeoApiConfig(partner: '999999', apiKey: ''),
			['handler' => $this->handlerStack()],
		);

		$this->mockResponse(200, '<PoiList/>');
		$geo->servicePoints()->nearest(zone: '1000')->get();

		$this->assertTrue($this->lastRequest()->hasHeader('x-api-key'));
	}
}
