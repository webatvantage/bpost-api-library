<?php

namespace Webatvantage\Bpost\Api\Parcel\Enums;

/**
 * The warranty bands the announcement service recognises.
 *
 * Unlike the Shipping Manager's, this list includes the basic band as a value of its own, so
 * additionalInsurance carries maxAmount 1 rather than there being a separate element.
 */
enum InsuranceAmount: int
{
	case UpTo500 = 1;
	case UpTo2500 = 2;
	case UpTo5000 = 3;
}
