<?php

namespace Webatvantage\Bpost\Api\Geo\Enums;

enum Weekday: string
{
	case Monday = 'Monday';
	case Tuesday = 'Tuesday';
	case Wednesday = 'Wednesday';
	case Thursday = 'Thursday';
	case Friday = 'Friday';
	case Saturday = 'Saturday';
	case Sunday = 'Sunday';

	/**
	 * ISO-8601 day number, Monday being 1.
	 */
	public function index(): int
	{
		return match ($this)
		{
			self::Monday => 1,
			self::Tuesday => 2,
			self::Wednesday => 3,
			self::Thursday => 4,
			self::Friday => 5,
			self::Saturday => 6,
			self::Sunday => 7,
		};
	}
}
