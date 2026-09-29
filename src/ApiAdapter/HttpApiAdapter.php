<?php

namespace Webatvantage\Bpost\Api\ApiAdapter;

use Closure;
use Dom\Element;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleLogMiddleware\LogMiddleware;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use Webatvantage\Bpost\Api\Contracts\Request;
use Webatvantage\Bpost\Api\Exceptions\TransporterException;
use Webatvantage\Bpost\Api\Exceptions\UnserializableResponseException;
use Webatvantage\Bpost\Api\Support\Xml;

class HttpApiAdapter
{
	private readonly Client $client;

	private readonly string $baseUri;

	/** @var (Closure(RequestInterface, ResponseInterface): void)|null */
	private ?Closure $debugCallback;

	/**
	 * @param string $baseUri
	 * @param array<string, string> $defaultHeaders
	 * @param array<string, mixed> $httpClientOptions
	 * @param LoggerInterface|null $logger
	 * @param (Closure(RequestInterface $request, ResponseInterface $response): void)|null $debugCallback
	 */
	public function __construct(
		string $baseUri,
		private readonly array $defaultHeaders = [],
		array $httpClientOptions = [],
		?LoggerInterface $logger = null,
		?Closure $debugCallback = null,
	) {
		$this->debugCallback = $debugCallback;
		$this->baseUri = rtrim($baseUri, '/');

		$handler = $httpClientOptions['handler'] ?? HandlerStack::create();

		if ($logger !== null && $handler instanceof HandlerStack)
		{
			$handler->push(new LogMiddleware($logger));
		}

		$this->client = new Client([
			...$httpClientOptions,
			'handler' => $handler,
			'http_errors' => false,
		]);
	}

	/**
	 * Send a request and hand back its parsed body.
	 *
	 * Returns the root element for an XML response, and a string otherwise — including the empty
	 * string, which is what a successful Create Order answers with.
	 */
	public function request(Request $request): Element|string
	{
		$headers = [...$this->defaultHeaders, ...$request->getHeaders()];

		try
		{
			$psrRequest = $request->toRequest($headers, $this->baseUri);
			$response = $this->client->send($psrRequest);
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

		$xml = Xml::tryParse($contents);

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
}
