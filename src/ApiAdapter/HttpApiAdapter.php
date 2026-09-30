<?php

namespace Webatvantage\Bpost\Api\ApiAdapter;

use Closure;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Promise\PromiseInterface;
use GuzzleLogMiddleware\LogMiddleware;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use Webatvantage\Bpost\Api\Contracts\Request;
use Webatvantage\Bpost\Api\Exceptions\TransporterException;
use Webatvantage\Bpost\Api\Exceptions\UnserializableResponseException;
use Webatvantage\Bpost\Api\Support\XmlDocument;
use Webatvantage\Bpost\Api\Support\XmlElement;

class HttpApiAdapter
{
	/** Guzzle request option carrying the decision for the request being sent. */
	private const string LOGGING_OPTION = 'bpost_logging';

	private readonly Client $client;

	private readonly string $baseUri;

	/** @var (Closure(RequestInterface, ResponseInterface): void)|null */
	private ?Closure $debugCallback;

	private bool $logging;

	/**
	 * @param string $baseUri
	 * @param array<string, string> $defaultHeaders
	 * @param array<string, mixed> $httpClientOptions
	 * @param LoggerInterface|null $logger
	 * @param (Closure(RequestInterface $request, ResponseInterface $response): void)|null $debugCallback
	 * @param bool $logging
	 */
	public function __construct(
		string $baseUri,
		private readonly array $defaultHeaders = [],
		array $httpClientOptions = [],
		?LoggerInterface $logger = null,
		?Closure $debugCallback = null,
		bool $logging = true,
	) {
		$this->debugCallback = $debugCallback;
		$this->logging = $logging;
		$this->baseUri = rtrim($baseUri, '/');

		$handler = $httpClientOptions['handler'] ?? HandlerStack::create();

		if ($logger !== null && $handler instanceof HandlerStack)
		{
			// Cloned first: one set of client options is shared by every service, so pushing onto
			// the caller's stack would stack a second copy of the middleware per service and log
			// each request once more for each of them. The clone copies the middleware list and
			// keeps the same underlying transport.
			$handler = clone $handler;
			$handler->push(self::conditionalLogging(new LogMiddleware(logger: $logger, logStatistics: true)));
		}

		$this->client = new Client([
			...$httpClientOptions,
			'handler' => $handler,
			'http_errors' => false,
		]);
	}

	/**
	 * Send a request and hand back its parsed body.
	 */
	public function request(Request $request): XmlElement|string
	{
		$headers = [...$this->defaultHeaders, ...$request->getHeaders()];

		try
		{
			$psrRequest = $request->toRequest($headers, $this->baseUri);
			$response = $this->client->send($psrRequest, [
				self::LOGGING_OPTION => $request->isLogging() ?? $this->logging,
			]);
		}
		catch (ClientExceptionInterface $clientException)
		{
			throw new TransporterException($clientException);
		}

		$contents = (string)$response->getBody();
		$statusCode = $response->getStatusCode();

		if (isset($this->debugCallback))
		{
			($this->debugCallback)($psrRequest, $response);
		}

		if ($statusCode < 200 || $statusCode > 299)
		{
			throw ApiExceptionFactory::fromResponse($statusCode, $contents);
		}

		if (!$request->expectsXml() || trim($contents) === '')
		{
			return $contents;
		}

		$xml = XmlDocument::tryParse($contents);

		if ($xml === null)
		{
			throw new UnserializableResponseException(
				message: 'The response body was not well-formed XML.',
				statusCode: $statusCode,
				body: $contents,
			);
		}

		return $xml;
	}

	/**
	 * @param (Closure(RequestInterface $request, ResponseInterface $response): void)|null $callback
	 *
	 * @return static
	 */
	public function setDebugCallback(?Closure $callback): static
	{
		$this->debugCallback = $callback;

		return $this;
	}

	public function isLogging(): bool
	{
		return $this->logging;
	}

	public function setLogging(bool $logging): static
	{
		$this->logging = $logging;

		return $this;
	}

	/**
	 * Wrap the log middleware so a silenced request skips it instead of reaching it.
	 */
	private static function conditionalLogging(LogMiddleware $middleware): Closure
	{
		return static function (callable $handler) use ($middleware): Closure {
			$logged = $middleware($handler);

			return static function (RequestInterface $request, array $options) use ($handler, $logged): PromiseInterface {
				return ($options[self::LOGGING_OPTION] ?? true)
					? $logged($request, $options)
					: $handler($request, $options);
			};
		};
	}
}
