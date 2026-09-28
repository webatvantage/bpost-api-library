<?php

namespace Webatvantage\Bpost\Api\Shm\DataObjects\Options\Flags;

use Webatvantage\Bpost\Api\DataObjects\Options\Flag;

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
