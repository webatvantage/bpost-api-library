<?php

namespace Webatvantage\Bpost\Api\Shm\DataObjects\Option\Flags;

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
