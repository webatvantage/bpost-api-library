<?php

namespace Webatvantage\Bpost\Api\Contracts;

use Closure;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Something whose requests and responses can be handed to a callback.
 */
interface Debuggable
{
	/**
	 * @param (Closure(RequestInterface $request, ResponseInterface $response): void)|null $callback
	 *
	 * @return static
	 */
	public function withDebug(?Closure $callback): static;
}
