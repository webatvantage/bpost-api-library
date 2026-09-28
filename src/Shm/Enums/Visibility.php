<?php

namespace Webatvantage\Bpost\Api\Shm\Enums;

/**
 * Whether a delivery method is offered to the consumer in the Shipping Manager front end.
 */
enum Visibility: string
{
	case Visible = 'VISIBLE';
	case GreyedOut = 'GREYED_OUT';
	case Invisible = 'INVISIBLE';
}
