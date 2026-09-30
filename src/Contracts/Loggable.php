<?php

namespace Webatvantage\Bpost\Api\Contracts;

/**
 * Something whose calls can be kept out of the log.
 *
 * Implemented at each level a caller can reach — the whole client, one service, one resource, one
 * call — so the same pair of names means the same thing wherever it is found, and the narrowest
 * setting wins.
 */
interface Loggable
{
	public function withLogging(bool $logging = true): static;

	public function withoutLogging(): static;
}
