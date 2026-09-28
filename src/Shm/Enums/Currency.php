<?php

namespace Webatvantage\Bpost\Api\Shm\Enums;

/**
 * The currencies customs values may be declared in.
 *
 * Anything else has to be converted before sending. Note amtPostagePaidByAddresse is always in
 * euro regardless of what this says.
 */
enum Currency: string
{
	case EUR = 'EUR';
	case GBP = 'GBP';
	case USD = 'USD';
	case CNY = 'CNY';
}
