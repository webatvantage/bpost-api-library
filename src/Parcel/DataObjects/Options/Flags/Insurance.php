<?php

namespace Webatvantage\Bpost\Api\Parcel\DataObjects\Options\Flags;

use Webatvantage\Bpost\Api\DataObjects\Options\Flag;

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
