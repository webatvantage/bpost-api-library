<?php

namespace Webatvantage\Bpost\Api\Parcel\DataObjects\Options;

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
