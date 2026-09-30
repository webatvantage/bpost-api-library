<?php

namespace Webatvantage\Bpost\Api\Tests\Doubles;

use Psr\Log\AbstractLogger;
use Stringable;

/**
 * Counts what the Guzzle log middleware writes, and at which level.
 */
class SpyLogger extends AbstractLogger
{
	/** @var array<int, array{level: string, message: string}> */
	public array $records = [];

	/**
	 * @param array<string, mixed> $context
	 */
	public function log($level, string|Stringable $message, array $context = []): void
	{
		$this->records[] = ['level' => (string)$level, 'message' => (string)$message];
	}

	/**
	 * The level the named record was written at, or null when it was not written at all.
	 */
	public function levelOf(string $message): ?string
	{
		foreach ($this->records as $record)
		{
			if ($record['message'] === $message)
			{
				return $record['level'];
			}
		}

		return null;
	}
}
