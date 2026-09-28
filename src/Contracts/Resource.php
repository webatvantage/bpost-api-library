<?php

namespace Webatvantage\Bpost\Api\Contracts;

use Closure;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Webatvantage\Bpost\Api\ApiAdapter\HttpApiAdapter;

abstract class Resource
{
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
}
