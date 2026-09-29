<?php

namespace Webatvantage\Bpost\Api\Shm\DataObjects\Options\Flags;

/**
 * Attempt a second delivery after a failed first attempt.
 */
class AutomaticSecondPresentation extends ShmFlag
{
	protected function tagName(): string
	{
		return 'automaticSecondPresentation';
	}
}
