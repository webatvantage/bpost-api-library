<?php

namespace Webatvantage\Bpost\Api\Geo;

use Closure;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use Webatvantage\Bpost\Api\ApiAdapter\HttpApiAdapter;
use Webatvantage\Bpost\Api\Contracts\Debuggable;
use Webatvantage\Bpost\Api\Contracts\Loggable;
use Webatvantage\Bpost\Api\Geo\Resources\ServicePointResource;

/**
 * The bpost Geolocator: pick-up points, parcel points and parcel lockers.
 */
readonly class GeoApiClient implements Debuggable, Loggable
{
	private HttpApiAdapter $apiAdapter;

	/**
	 * @param array<string, mixed> $httpClientOptions
	 */
	public function __construct(
		private GeoApiConfig $config,
		array $httpClientOptions = [],
		?LoggerInterface $logger = null,
	) {
		// Without this bpost truncates a Get All Service Points response over 10 MB.
		$headers = ['Accept-Encoding' => 'gzip'];

		if (isset($config->apiKey))
		{
			// Mandatory on the pudo.bpost.cloud domain since manual section B.4.1.0.
			$headers['x-api-key'] = $config->apiKey;
		}

		$this->apiAdapter = new HttpApiAdapter(
			baseUri: $config->baseUri,
			defaultHeaders: $headers,
			httpClientOptions: $httpClientOptions,
			logger: $logger,
		);
	}

	/**
	 * Log every call this client makes, or keep them all out of the log.
	 */
	public function withLogging(bool $logging = true): static
	{
		$this->apiAdapter->withLogging($logging);

		return $this;
	}

	public function withoutLogging(): static
	{
		return $this->withLogging(false);
	}

	/**
	 * Hand every request and response this client sends to a callback.
	 *
	 * @param (Closure(RequestInterface $request, ResponseInterface $response): void)|null $callback
	 *
	 * @return static
	 */
	public function withDebug(?Closure $callback): static
	{
		$this->apiAdapter->withDebug($callback);

		return $this;
	}

	public function servicePoints(): ServicePointResource
	{
		return new ServicePointResource($this->apiAdapter, $this->config);
	}
}
