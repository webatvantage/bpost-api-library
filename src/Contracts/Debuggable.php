<?php

namespace Webatvantage\Bpost\Api\Contracts;

use Closure;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Something whose requests and responses can be handed to a callback.
 *
 * Separate from Loggable: a logger is told what happened after the fact, while this gets the PSR-7
 * pair itself and is what you reach for when bpost rejects a document and the body is the only
 * thing that explains why. The levels mirror Loggable's, and the narrowest setting wins.
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
