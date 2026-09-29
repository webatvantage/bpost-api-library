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

	private bool $logging = true;

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

		return $this->shm ??= new ShmApiClient($this->config->shm, $this->httpClientOptions, $this->logger)
			->withLogging($this->logging);
	}

	public function geo(): GeoApiClient
	{
		if ($this->config->geo === null)
		{
			throw MissingConfigurationException::forDomain('geo');
		}

		return $this->geo ??= new GeoApiClient($this->config->geo, $this->httpClientOptions, $this->logger)
			->withLogging($this->logging);
	}

	public function parcel(): ParcelApiClient
	{
		if ($this->config->parcel === null)
		{
			throw MissingConfigurationException::forDomain('parcel');
		}

		return $this->parcel ??= new ParcelApiClient($this->config->parcel, $this->httpClientOptions, $this->logger)
			->withLogging($this->logging);
	}

	/**
	 * Log every call to every service, or keep them all out of the log.
	 *
	 * This reaches the domain clients you already took hold of as well as the ones you have not.
	 */
	public function withLogging(bool $logging = true): static
	{
		$this->logging = $logging;

		$this->shm?->withLogging($logging);
		$this->geo?->withLogging($logging);
		$this->parcel?->withLogging($logging);

		return $this;
	}

	public function withoutLogging(): static
	{
		return $this->withLogging(false);
	}
}
