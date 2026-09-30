<?php

namespace Webatvantage\Bpost\Api\Enums;

/**
 * Every language bpost accepts anywhere.
 *
 * Each service takes a subset: Shipping Manager messaging allows all four, the Geolocator allows
 * NL and FR only. The subsets are named here so a caller can offer the right set; enforcing one
 * is still the domain's job.
 */
enum Language: string
{
	case EN = 'EN';
	case NL = 'NL';
	case FR = 'FR';
	case DE = 'DE';

	/**
	 * The two the Geolocator takes. All four of its operations document Language as NL or FR, and
	 * ignore anything else rather than reporting it.
	 *
	 * @return array<int, self>
	 */
	public static function forGeolocator(): array
	{
		return [self::NL, self::FR];
	}
}
