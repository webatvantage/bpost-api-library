<?php

namespace Webatvantage\Bpost\Api\Contracts;

/**
 * Something whose calls can be kept out of the log.
 */
interface Loggable
{
	public function withLogging(bool $logging = true): static;

	public function withoutLogging(): static;
}
