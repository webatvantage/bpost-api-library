<?php

namespace Webatvantage\Bpost\Api\Contracts;

use Closure;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Webatvantage\Bpost\Api\ApiAdapter\HttpApiAdapter;

abstract class Resource
{
	private ?bool $logging = null;

	public function __construct(protected readonly HttpApiAdapter $apiAdapter) {}

	/**
	 * @param (Closure(RequestInterface $request, ResponseInterface $response): void)|null $callback
	 *
	 * @return static
	 */
	public function debug(?Closure $callback): static
	{
		$this->apiAdapter->setDebugCallback($callback);

		return $this;
	}

	/**
	 * Log the calls made through this resource, or keep them out of the log.
	 */
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
	 * @template TRequest of Request
	 *
	 * @param TRequest $request
	 *
	 * @return TRequest
	 */
	protected function prepare(Request $request): Request
	{
		return $this->logging === null ? $request : $request->withLogging($this->logging);
	}
}
