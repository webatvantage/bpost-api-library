<?php

namespace Webatvantage\Bpost\Api;

use Psr\Log\LoggerInterface;
use Webatvantage\Bpost\Api\Exceptions\MissingConfigurationException;
use Webatvantage\Bpost\Api\Geo\GeoApiClient;

/**
 * One entry point for the bpost services.
 *
 * They are genuinely separate APIs — different hosts, different credentials, different schemas — so
 * each is also usable on its own (`new GeoApiClient($config)`). This exists so an integration that
 * talks to more than one does not have to wire up each of them by hand.
 */
readonly class BpostApiClient
{
	/**
	 * @param array<string, mixed> $httpClientOptions
	 */
	public function __construct(
		private BpostApiConfig $config,
		private array $httpClientOptions = [],
		private ?LoggerInterface $logger = null,
	) {}

	public function geo(): GeoApiClient
	{
		if ($this->config->geo === null)
		{
			throw MissingConfigurationException::forDomain('geo');
		}

		return new GeoApiClient($this->config->geo, $this->httpClientOptions, $this->logger);
	}
}
