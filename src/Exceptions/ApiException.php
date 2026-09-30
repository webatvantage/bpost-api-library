<?php

namespace Webatvantage\Bpost\Api\Exceptions;

use Throwable;

/**
 * The request reached bpost and bpost refused it.
 *
 * The raw body is kept because bpost's fault documents carry detail the message cannot hold, and
 * because an unrecognised body shape still needs to reach the caller intact.
 */
class ApiException extends BpostException
{
	/**
	 * @param int $statusCode What bpost answered, or 0 where the status is not what went wrong —
	 *                        a 2xx whose body this library could not read leaves it unset rather
	 *                        than naming a code nobody checked
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
