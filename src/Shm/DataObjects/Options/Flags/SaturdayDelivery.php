<?php

namespace Webatvantage\Bpost\Api\Shm\DataObjects\Options\Flags;

use Webatvantage\Bpost\Api\Contracts\Flag;

/**
 * Deliver on Saturday.
 */
class SaturdayDelivery extends Flag
{
	protected function tagName(): string
	{
		return 'saturdayDelivery';
	}
}
