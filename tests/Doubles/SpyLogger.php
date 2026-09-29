<?php

namespace Webatvantage\Bpost\Api\Tests\Doubles;

use Psr\Log\AbstractLogger;
use Stringable;

/**
 * Counts what the Guzzle log middleware writes.
 */
class SpyLogger extends AbstractLogger
{
	/** @var array<int, string> */
	public array $records = [];

	/**
	 * @param array<string, mixed> $context
	 */
	public function log($level, string|Stringable $message, array $context = []): void
	{
		$this->records[] = (string)$message;
	}
}
