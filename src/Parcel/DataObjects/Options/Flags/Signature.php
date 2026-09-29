<?php

namespace Webatvantage\Bpost\Api\Parcel\DataObjects\Options\Flags;

/**
 * Require a signature on delivery.
 */
class Signature extends ParcelFlag
{
	protected function tagName(): string
	{
		return 'signature';
	}
}
