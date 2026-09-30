<?php

namespace Webatvantage\Bpost\Api;

use Closure;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use Webatvantage\Bpost\Api\Contracts\Debuggable;
use Webatvantage\Bpost\Api\Contracts\Loggable;
use Webatvantage\Bpost\Api\Exceptions\MissingConfigurationException;
use Webatvantage\Bpost\Api\Geo\GeoApiClient;
use Webatvantage\Bpost\Api\Parcel\ParcelApiClient;
use Webatvantage\Bpost\Api\Shm\ShmApiClient;

/**
 * One entry point for the bpost services.
 */
class BpostApiClient implements Debuggable, Loggable
{
	private ?ShmApiClient $shm = null;

	private ?GeoApiClient $geo = null;

	private ?ParcelApiClient $parcel = null;

	private bool $logging = false;

	/** @var (Closure(RequestInterface, ResponseInterface): void)|null */
	private ?Closure $debugCallback = null;

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

		return $this->shm ??= new ShmApiClient(
			config: $this->config->shm,
			httpClientOptions: $this->httpClientOptions,
			logger: $this->logger,
			logHandler: $this->config->logHandler,
		)
			->withLogging($this->logging)
			->withDebug($this->debugCallback);
	}

	public function geo(): GeoApiClient
	{
		if ($this->config->geo === null)
		{
			throw MissingConfigurationException::forDomain('geo');
		}

		return $this->geo ??= new GeoApiClient(
			config: $this->config->geo,
			httpClientOptions: $this->httpClientOptions,
			logger: $this->logger,
			logHandler: $this->config->logHandler,
		)
			->withLogging($this->logging)
			->withDebug($this->debugCallback);
	}

	public function parcel(): ParcelApiClient
	{
		if ($this->config->parcel === null)
		{
			throw MissingConfigurationException::forDomain('parcel');
		}

		return $this->parcel ??= new ParcelApiClient(
			config: $this->config->parcel,
			httpClientOptions: $this->httpClientOptions,
			logger: $this->logger,
			logHandler: $this->config->logHandler,
		)
			->withLogging($this->logging)
			->withDebug($this->debugCallback);
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

	/**
	 * Hand every request and response of every service to a callback.
	 *
	 * This reaches the domain clients you already took hold of as well as the ones you have not.
	 *
	 * @param (Closure(RequestInterface $request, ResponseInterface $response): void)|null $callback
	 *
	 * @return static
	 */
	public function withDebug(?Closure $callback): static
	{
		$this->debugCallback = $callback;

		$this->shm?->withDebug($callback);
		$this->geo?->withDebug($callback);
		$this->parcel?->withDebug($callback);

		return $this;
	}
}
