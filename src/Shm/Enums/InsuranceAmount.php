<?php

namespace Webatvantage\Bpost\Api\Shm\Enums;

/**
 * The bands additional warranty comes in.
 *
 * 3.x also defined bands up to 25 000 EUR, but its own validation rejected them: bpost capped
 * additional warranty at 5 000 EUR in SHM API 3.3.24 and the higher constants had been unusable
 * ever since.
 */
enum InsuranceAmount: int
{
	case UpTo2500 = 2;
	case UpTo5000 = 3;
}
