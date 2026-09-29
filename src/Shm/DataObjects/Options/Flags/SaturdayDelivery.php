<?php

namespace Webatvantage\Bpost\Api\Shm\DataObjects\Options\Flags;

/**
 * Deliver on Saturday.
 */
class SaturdayDelivery extends ShmFlag
{
	protected function tagName(): string
	{
		return 'saturdayDelivery';
	}
}
