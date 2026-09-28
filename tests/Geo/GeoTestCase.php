<?php

namespace Webatvantage\Bpost\Api\Tests\Geo;

use Webatvantage\Bpost\Api\Geo\GeoApiClient;
use Webatvantage\Bpost\Api\Geo\GeoApiConfig;
use Webatvantage\Bpost\Api\Tests\TestCase;

abstract class GeoTestCase extends TestCase
{
	protected function config(): GeoApiConfig
	{
		return new GeoApiConfig(
			partner: '999999',
			apiKey: 'test-api-key',
			appId: 'A001',
			baseUri: 'https://pudo.bpost.cloud',
		);
	}

	protected function client(): GeoApiClient
	{
		return new GeoApiClient($this->config(), ['handler' => $this->handlerStack()]);
	}

	/**
	 * The query bpost actually received, as an associative array. Repeated keys become a list.
	 *
	 * @return array<string, string|list<string>>
	 */
	protected function sentQuery(): array
	{
		$query = [];

		foreach (explode('&', $this->lastRequest()->getUri()->getQuery()) as $pair)
		{
			if ($pair === '')
			{
				continue;
			}

			[$key, $value] = array_pad(explode('=', $pair, 2), 2, '');
			$key = rawurldecode($key);
			$value = rawurldecode($value);

			if (!array_key_exists($key, $query))
			{
				$query[$key] = $value;

				continue;
			}

			$query[$key] = array_merge((array)$query[$key], [$value]);
		}

		return $query;
	}
}
