<?php

namespace Webatvantage\Bpost\Api\Contracts;

use Closure;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Webatvantage\Bpost\Api\ApiAdapter\HttpApiAdapter;

abstract class Resource implements Debuggable, Loggable
{
	private ?bool $logging = null;

	/** @var (Closure(RequestInterface, ResponseInterface): void)|null */
	public private(set) ?Closure $debugCallback = null;

	public function __construct(protected readonly HttpApiAdapter $apiAdapter) {}

	/**
	 * Hand the request and response of the calls made through this resource to a callback.
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
		if ($this->logging !== null)
		{
			$request->withLogging($this->logging);
		}

		if ($this->debugCallback !== null)
		{
			$request->withDebug($this->debugCallback);
		}

		return $request;
	}
}
