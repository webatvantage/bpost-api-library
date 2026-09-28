<?php

namespace Webatvantage\Bpost\Api\Parcel\DataObjects\Options;

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
