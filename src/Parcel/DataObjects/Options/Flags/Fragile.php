<?php

namespace Webatvantage\Bpost\Api\Parcel\DataObjects\Options\Flags;

/**
 * Mark the parcel fragile. bpack XL only, and bpost adds basic warranty with it.
 */
class Fragile extends ParcelFlag
{
	protected function tagName(): string
	{
		return 'fragile';
	}
}
