<?php

namespace Webatvantage\Bpost\Api\Exceptions;

use Psr\Http\Client\ClientExceptionInterface;

/**
 * The request never produced a response: DNS, TLS, connect or read timeout.
 */
class TransporterException extends BpostException
{
	public function __construct(ClientExceptionInterface $exception)
	{
		parent::__construct($exception->getMessage(), 0, $exception);
	}
}
