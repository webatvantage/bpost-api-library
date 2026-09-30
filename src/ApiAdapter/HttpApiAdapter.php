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
use Webatvantage\Bpost\Api\Contracts\Debuggable;
use Webatvantage\Bpost\Api\Contracts\Loggable;
use Webatvantage\Bpost\Api\Contracts\Request;
use Webatvantage\Bpost\Api\Exceptions\TransporterException;
use Webatvantage\Bpost\Api\Exceptions\UnserializableResponseException;
use Webatvantage\Bpost\Api\Support\XmlDocument;
use Webatvantage\Bpost\Api\Support\XmlElement;

class HttpApiAdapter implements Debuggable, Loggable
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
			// One set of client options is shared by every service, so the caller's own stack
			// would collect a copy of the middleware per service.
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
		$debugCallback = $request->debugCallback ?? $this->debugCallback;

		if ($debugCallback !== null)
		{
			$debugCallback($psrRequest, $response);
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
	 * Hand every request and response this client sends to a callback.
	 *
	 * A single call or a single resource can name its own instead, which wins over this one.
	 *
	 * @param (Closure(RequestInterface $request, ResponseInterface $response): void)|null $callback
	 *
	 * @return static
	 */
	public function withDebug(?Closure $callback): static
	{
		$this->debugCallback = $callback;

		return $this;
	}

	public function withLogging(bool $logging = true): static
	{
		$this->logging = $logging;

		return $this;
	}

	public function withoutLogging(): static
	{
		return $this->withLogging(false);
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
