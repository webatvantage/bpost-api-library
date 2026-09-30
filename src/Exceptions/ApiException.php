<?php

namespace Webatvantage\Bpost\Api\Exceptions;

use Throwable;

/**
 * The request reached bpost and bpost refused it.
 */
class ApiException extends BpostException
{
	/**
	 * @param int $statusCode What bpost answered or 0 where the status is not what went wrong
	 */
	public function __construct(
		string $message,
		public readonly int $statusCode = 0,
		public readonly string $body = '',
		?Throwable $previous = null,
	) {
		parent::__construct($message, $statusCode, $previous);
	}
}
