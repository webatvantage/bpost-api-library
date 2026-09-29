<?php

namespace Webatvantage\Bpost\Api\Parcel\DataObjects\Options\Flags;

/**
 * Basic warranty, up to 500 EUR.
 */
class Insurance extends ParcelFlag
{
	protected function tagName(): string
	{
		return 'insurance';
	}
}
