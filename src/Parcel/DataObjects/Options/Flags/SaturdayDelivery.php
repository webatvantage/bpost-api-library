<?php

namespace Webatvantage\Bpost\Api\Parcel\DataObjects\Options\Flags;

use Webatvantage\Bpost\Api\DataObjects\Options\Flag;

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
