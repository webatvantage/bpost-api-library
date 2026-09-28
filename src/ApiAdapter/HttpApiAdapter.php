<?php

namespace Webatvantage\Bpost\Api\ApiAdapter;

use Closure;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleLogMiddleware\LogMiddleware;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Log\LoggerInterface;
use SimpleXMLElement;
use Webatvantage\Bpost\Api\Exceptions\ApiException;
use Webatvantage\Bpost\Api\Exceptions\BusinessException;
use Webatvantage\Bpost\Api\Exceptions\InvalidResponseException;
use Webatvantage\Bpost\Api\Exceptions\SystemException;
use Webatvantage\Bpost\Api\Exceptions\TransporterException;
use Webatvantage\Bpost\Api\Exceptions\UnserializableResponseException;
use Webatvantage\Bpost\Api\Requests\Request;
use Webatvantage\Bpost\Api\Support\Xml;

/**
 * The single HTTP seam for all three bpost services.
 *
 * It knows nothing about any of them: the domain client supplies the base URI and whatever
 * authentication headers that service wants (Basic for Shipping Manager and Parcel, x-api-key for
 * the Geolocator), and each Request carries its own Accept and Content-Type.
 */
class HttpApiAdapter
{
	private readonly ClientInterface $client;

	private ?Closure $debugCallback;

	/**
	 * @param array<string, string> $defaultHeaders
	 * @param array<string, mixed> $httpClientOptions
	 */
	public function __construct(
		string $baseUri,
		private readonly array $defaultHeaders = [],
		array $httpClientOptions = [],
		?LoggerInterface $logger = null,
		?Closure $debugCallback = null,
	) {
		$this->debugCallback = $debugCallback;

		$handler = $httpClientOptions['handler'] ?? HandlerStack::create();

		if ($logger !== null && $handler instanceof HandlerStack)
		{
			$handler->push(new LogMiddleware($logger));
		}

		$this->client = new Client([
			...$httpClientOptions,
			'handler' => $handler,
			'base_uri' => $baseUri,
			'http_errors' => false,
		]);
	}

	/**
	 * Send a request and hand back its parsed body.
	 *
	 * Returns a SimpleXMLElement for an XML response, and a string otherwise — including the empty
	 * string, which is what a successful Create Order answers with.
	 */
	public function request(Request $request): SimpleXMLElement|string
	{
		$headers = [...$this->defaultHeaders, ...$request->getHeaders()];

		try
		{
			$psrRequest = $request->toRequest($headers);
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
			($this->debugCallback)($contents, (string)$psrRequest->getUri(), (string)$psrRequest->getBody());
		}

		if ($statusCode < 200 || $statusCode > 299)
		{
			throw self::toException($statusCode, $contents);
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

	public function setDebugCallback(?Closure $callback): static
	{
		$this->debugCallback = $callback;

		return $this;
	}

	/**
	 * Map a bpost fault document onto an exception.
	 *
	 * The body is always carried through, since bpost's message is the only thing that says which
	 * field it objected to.
	 */
	private static function toException(int $statusCode, string $contents): ApiException
	{
		$xml = Xml::tryParse($contents);

		if ($xml === null)
		{
			return new InvalidResponseException(
				self::fallbackMessage($statusCode, $contents),
				$statusCode,
				$contents,
			);
		}

		$message = self::firstValue($xml, 'message') ?? self::fallbackMessage($statusCode, $contents);
		$code = self::firstValue($xml, 'code');

		return match ($xml->getName())
		{
			'businessException' => new BusinessException($message, (int)($code ?? $statusCode), $contents),
			'systemException' => new SystemException($message, $statusCode, $contents),
			default => new InvalidResponseException($message, $statusCode, $contents),
		};
	}

	/**
	 * bpost's fault documents bind `code` and `message` to a different namespace than their own
	 * root element, and which namespace varies between the business and system shapes, so they are
	 * matched on local name.
	 */
	private static function firstValue(SimpleXMLElement $xml, string $localName): ?string
	{
		$found = $xml->xpath(sprintf('//*[local-name()="%s"]', $localName));

		if (!is_array($found) || count($found) === 0)
		{
			return null;
		}

		$value = trim((string)$found[0]);

		return $value === '' ? null : $value;
	}

	private static function fallbackMessage(int $statusCode, string $contents): string
	{
		$body = trim($contents);

		if ($body === '')
		{
			return sprintf('bpost answered HTTP %d with an empty body.', $statusCode);
		}

		if (mb_strlen($body) > 500)
		{
			$body = mb_substr($body, 0, 500) . '…';
		}

		return sprintf('bpost answered HTTP %d: %s', $statusCode, $body);
	}
}
