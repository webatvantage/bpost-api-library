<?php

namespace Webatvantage\Bpost\Api\Parcel\DataObjects\Options;

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
