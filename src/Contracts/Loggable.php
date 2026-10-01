<?php

namespace Webatvantage\Bpost\Api\Contracts;

/**
 * Something whose calls carry a logging choice, sent as a request option for a log handler to read.
 */
interface Loggable
{
	public function withLogging(bool $logging = true): static;

	public function withoutLogging(): static;
}
