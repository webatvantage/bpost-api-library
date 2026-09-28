<?php

namespace Webatvantage\Bpost\Api;

use Psr\Log\LoggerInterface;
use Webatvantage\Bpost\Api\Exceptions\MissingConfigurationException;
use Webatvantage\Bpost\Api\Geo\GeoApiClient;
use Webatvantage\Bpost\Api\Parcel\ParcelApiClient;
use Webatvantage\Bpost\Api\Shm\ShmApiClient;

/**
 * One entry point for the bpost services.
 */
class BpostApiClient
{
	private ?ShmApiClient $shm = null;

	private ?GeoApiClient $geo = null;

	private ?ParcelApiClient $parcel = null;

	/**
	 * @param array<string, mixed> $httpClientOptions
	 */
	public function __construct(
		private readonly BpostApiConfig $config,
		private readonly array $httpClientOptions = [],
		private readonly ?LoggerInterface $logger = null,
	) {}

	public function shm(): ShmApiClient
	{
		if ($this->config->shm === null)
		{
			throw MissingConfigurationException::forDomain('shm');
		}

		return $this->shm ??= new ShmApiClient($this->config->shm, $this->httpClientOptions, $this->logger);
	}

	public function geo(): GeoApiClient
	{
		if ($this->config->geo === null)
		{
			throw MissingConfigurationException::forDomain('geo');
		}

		return $this->geo ??= new GeoApiClient($this->config->geo, $this->httpClientOptions, $this->logger);
	}

	public function parcel(): ParcelApiClient
	{
		if ($this->config->parcel === null)
		{
			throw MissingConfigurationException::forDomain('parcel');
		}

		return $this->parcel ??= new ParcelApiClient($this->config->parcel, $this->httpClientOptions, $this->logger);
	}
}
