<?php

namespace Webatvantage\Bpost\Api\ApiAdapter;

use Closure;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\HandlerStack;
use GuzzleLogMiddleware\Handler\HandlerInterface;
use GuzzleLogMiddleware\Handler\LogLevelStrategy\ThresholdStrategy;
use GuzzleLogMiddleware\Handler\MultiRecordArrayHandler;
use GuzzleLogMiddleware\LogMiddleware;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use Webatvantage\Bpost\Api\Contracts\Debuggable;
use Webatvantage\Bpost\Api\Contracts\Loggable;
use Webatvantage\Bpost\Api\Contracts\Request;
use Webatvantage\Bpost\Api\Exceptions\BpostException;
use Webatvantage\Bpost\Api\Exceptions\TransporterException;
use Webatvantage\Bpost\Api\Exceptions\UnserializableResponseException;
use Webatvantage\Bpost\Api\Support\XmlDocument;
use Webatvantage\Bpost\Api\Support\XmlElement;

class HttpApiAdapter implements Debuggable, Loggable
{
	/** Guzzle request option carrying the decision for the request being sent. */
	protected const string LOGGING_OPTION_NAME = 'bpost_logging';

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
	 * @param HandlerInterface|null $logHandler What a log record is made of, defaulting to the
	 *                                          array shape with a level per status range
	 * @param (Closure(RequestInterface $request, ResponseInterface $response): void)|null $debugCallback
	 * @param bool $logging
	 */
	public function __construct(
		string $baseUri,
		private readonly array $defaultHeaders = [],
		array $httpClientOptions = [],
		?LoggerInterface $logger = null,
		?HandlerInterface $logHandler = null,
		?Closure $debugCallback = null,
		bool $logging = true,
	) {
		$this->debugCallback = $debugCallback;
		$this->logging = $logging;
		$this->baseUri = rtrim($baseUri, '/');

		$handler = $httpClientOptions['handler'] ?? HandlerStack::create();

		if (isset($logger) && $handler instanceof HandlerStack)
		{
			// One set of client options is shared by every service, so the caller's own stack
			// would collect a copy of the middleware per service.
			$handler = clone $handler;
			// The middleware runs even for a silenced call, because what reaches the log is decided
			// on the status and the handler is where that is known. Whoever supplied the handler,
			// it is wrapped here, so a silenced call still drops a record it brought.
			$handler->push(new LogMiddleware(
				logger: $logger,
				// Levels by status range, so a logger set above debug keeps only the failures.
				handler: new ConditionalLogHandler(
					handler: $logHandler ?? new MultiRecordArrayHandler(new ThresholdStrategy()),
					loggingOptionName: static::LOGGING_OPTION_NAME,
				),
				logStatistics: true,
			), 'logger');
		}

		$this->client = new Client([
			...$httpClientOptions,
			'handler' => $handler,
		]);
	}

	/**
	 * Send a request and hand back its parsed body.
	 *
	 * @throws BpostException
	 */
	public function request(Request $request): XmlElement|string
	{
		$headers = [...$this->defaultHeaders, ...$request->getHeaders()];

		$psrRequest = $request->toRequest($headers, $this->baseUri);

		try
		{
			$response = $this->client->send($psrRequest, [
				static::LOGGING_OPTION_NAME => $request->isLogging() ?? $this->logging,
			]);
		}
		// Catch a 4xx and a 5xx error
		catch (BadResponseException $badResponseException)
		{
			$response = $badResponseException->getResponse();
		}
		catch (ClientExceptionInterface $clientException)
		{
			throw new TransporterException($clientException);
		}

		$contents = mb_trim((string)$response->getBody());
		$statusCode = $response->getStatusCode();
		$debugCallback = $request->debugCallback ?? $this->debugCallback;

		if (isset($debugCallback))
		{
			$debugCallback($psrRequest, $response);
		}

		if ($statusCode < 200 || $statusCode > 299)
		{
			throw ApiExceptionFactory::fromResponse($statusCode, $contents, $badResponseException ?? null);
		}

		if (!$request->expectsXml() || $contents === '')
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
}
