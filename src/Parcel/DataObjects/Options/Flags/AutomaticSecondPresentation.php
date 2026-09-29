<?php

namespace Webatvantage\Bpost\Api\Parcel\DataObjects\Options\Flags;

/**
 * Attempt a second delivery after a failed first attempt.
 */
class AutomaticSecondPresentation extends ParcelFlag
{
	protected function tagName(): string
	{
		return 'automaticSecondPresentation';
	}
}
