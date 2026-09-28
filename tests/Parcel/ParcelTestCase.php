<?php

namespace Webatvantage\Bpost\Api\Tests\Parcel;

use RuntimeException;
use Webatvantage\Bpost\Api\Parcel\ParcelApiClient;
use Webatvantage\Bpost\Api\Parcel\ParcelApiConfig;
use Webatvantage\Bpost\Api\Tests\TestCase;

abstract class ParcelTestCase extends TestCase
{
	protected function config(): ParcelApiConfig
	{
		return new ParcelApiConfig(accountId: '123456', password: 'secret');
	}

	protected function client(): ParcelApiClient
	{
		return new ParcelApiClient($this->config(), ['handler' => $this->handlerStack()]);
	}

	protected function fixture(string $name): string
	{
		$contents = file_get_contents(__DIR__ . '/../Fixtures/Parcel/' . $name);

		if ($contents === false)
		{
			throw new RuntimeException("Fixture not found: {$name}");
		}

		return $contents;
	}
}
