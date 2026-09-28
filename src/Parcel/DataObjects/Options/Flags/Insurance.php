<?php

namespace Webatvantage\Bpost\Api\Parcel\DataObjects\Options\Flags;

use Webatvantage\Bpost\Api\Contracts\Flag;

/**
 * Basic warranty, up to 500 EUR.
 */
class Insurance extends Flag
{
	protected function tagName(): string
	{
		return 'insurance';
	}
}
