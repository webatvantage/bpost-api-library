<?php

namespace Webatvantage\Bpost\Api\Parcel\DataObjects\Options\Flags;

/**
 * Deliver on Saturday.
 */
class SaturdayDelivery extends ParcelFlag
{
	protected function tagName(): string
	{
		return 'saturdayDelivery';
	}
}
