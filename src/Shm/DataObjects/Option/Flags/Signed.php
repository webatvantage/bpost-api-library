<?php

namespace Webatvantage\Bpost\Api\Shm\DataObjects\Option\Flags;

/**
 * Require a signature on delivery.

 * Do not add this alongside an option that already includes one — cash on delivery, warranty and
 * automatic second presentation all do.
 */
class Signed extends Flag
{
	protected function tagName(): string
	{
		return 'signed';
	}
}
