<?php

namespace Webatvantage\Bpost\Api\Requests;

use BackedEnum;
use GuzzleHttp\Psr7\Request as PsrRequest;
use Psr\Http\Message\RequestInterface;
use Webatvantage\Bpost\Api\Enums\Method;
use Webatvantage\Bpost\Api\Traits\Conditionable;

/**
 * One call to one documented bpost endpoint.
 *
 * Accept and Content-Type live on the request rather than on the adapter: bpost versions each
 * operation's media type separately, so two calls to the same service routinely disagree.
 */
class Request
{
	use Conditionable;

	/**
	 * @param array<string, mixed> $parameters
	 * @param array<string, string> $headers
	 */
	public function __construct(
		protected readonly Method $method,
		protected string $resourceUri,
		protected array $parameters = [],
		protected ?string $body = null,
		protected array $headers = [],
		protected bool $expectsXml = true,
	) {}

	public function getMethod(): Method
	{
		return $this->method;
	}

	public function addParameter(string $name, mixed $value): static
	{
		$this->parameters[$name] = $value;

		return $this;
	}

	public function addHeader(string $name, string $value): static
	{
		$this->headers[$name] = $value;

		return $this;
	}

	/**
	 * @return array<string, string>
	 */
	public function getHeaders(): array
	{
		return $this->headers;
	}

	public function withBody(?string $body): static
	{
		$this->body = $body;

		return $this;
	}

	public function hasBody(): bool
	{
		return $this->body !== null && $this->body !== '';
	}

	public function expectsXml(): bool
	{
		return $this->expectsXml;
	}

	/**
	 * @param array<string, string> $headers
	 */
	public function toRequest(array $headers = []): RequestInterface
	{
		return new PsrRequest(
			$this->method->value,
			$this->buildUri(),
			$headers,
			$this->body,
		);
	}

	public function getUri(): string
	{
		return $this->buildUri();
	}

	protected function buildUri(): string
	{
		$pairs = [];

		foreach ($this->parameters as $name => $value)
		{
			foreach (is_array($value) ? $value : [$value] as $single)
			{
				if ($single === null)
				{
					continue;
				}

				$pairs[] = rawurlencode((string)$name) . '=' . rawurlencode(self::stringify($single));
			}
		}

		if (count($pairs) === 0)
		{
			return $this->resourceUri;
		}

		return $this->resourceUri . '?' . implode('&', $pairs);
	}

	/**
	 * Repeated parameters are emitted as `Key=a&Key=b`, not `Key[0]=a`.
	 *
	 * The Geolocator's AttributeFilter is documented that way and rejects the indexed form, which
	 * is what Guzzle's Query::build would produce.
	 */
	private static function stringify(mixed $value): string
	{
		if ($value instanceof BackedEnum)
		{
			return (string)$value->value;
		}

		if (is_bool($value))
		{
			return $value ? '1' : '0';
		}

		return (string)$value;
	}
}
