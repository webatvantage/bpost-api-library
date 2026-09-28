<?php

namespace Webatvantage\Bpost\Api\Parcel\DataObjects\Options\Flags;

use Webatvantage\Bpost\Api\Contracts\Flag;

/**
 * Require a signature on delivery.
 */
class Signature extends Flag
{
	protected function tagName(): string
	{
		return 'signature';
	}
}
