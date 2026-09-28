<?php

namespace Webatvantage\Bpost\Api\Shm\Enums;

/**
 * The bands additional warranty comes in.
 *
 * bpost capped additional warranty at 5 000 EUR in SHM API 3.3.24.
 */
enum InsuranceAmount: int
{
	case UpTo2500 = 2;
	case UpTo5000 = 3;
}
