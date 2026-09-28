<?php

namespace Webatvantage\Bpost\Api\Parcel\DataObjects\Options\Flags;

use Webatvantage\Bpost\Api\DataObjects\Options\Flag;

/**
 * Mark the parcel fragile. bpack XL only, and bpost adds basic warranty with it.
 */
class Fragile extends Flag
{
	protected function tagName(): string
	{
		return 'fragile';
	}
}
