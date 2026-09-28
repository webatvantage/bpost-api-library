<?php

namespace Webatvantage\Bpost\Api\Shm\Enums;

/**
 * Warranty, which bpost still calls insurance in the XML.
 */
enum InsuranceType: string
{
	/** Up to 500 EUR, with no value element. */
	case Basic = 'basicInsurance';

	/** Carries a value attribute saying which band applies. */
	case Additional = 'additionalInsurance';
}
