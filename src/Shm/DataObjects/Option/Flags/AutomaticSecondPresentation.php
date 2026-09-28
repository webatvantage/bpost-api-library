<?php

namespace Webatvantage\Bpost\Api\Shm\DataObjects\Option\Flags;

/**
 * Attempt a second delivery after a failed first attempt.
 */
class AutomaticSecondPresentation extends Flag
{
	protected function tagName(): string
	{
		return 'automaticSecondPresentation';
	}
}
