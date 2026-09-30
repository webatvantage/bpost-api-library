<?php

namespace Webatvantage\Bpost\Api\Enums;

/**
 * A day of the week.
 *
 * Shared because the Geolocator and the Shipping Manager both key opening hours by it, though
 * they spell the hours themselves quite differently.
 */
enum Weekday: string
{
	case Monday = 'Monday';
	case Tuesday = 'Tuesday';
	case Wednesday = 'Wednesday';
	case Thursday = 'Thursday';
	case Friday = 'Friday';
	case Saturday = 'Saturday';
	case Sunday = 'Sunday';
}
