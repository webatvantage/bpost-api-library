<?php

namespace Webatvantage\Bpost\Api\Parcel\DataObjects\Options\Flags;

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
