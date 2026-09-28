<?php

namespace Webatvantage\Bpost\Api\Enums;

/**
 * Every language bpost accepts anywhere.
 *
 * Each service takes a subset: Shipping Manager messaging allows all four, the Geolocator query
 * parameter allows NL and FR only. Validating the subset is the domain's job.
 */
enum Language: string
{
	case EN = 'EN';
	case NL = 'NL';
	case FR = 'FR';
	case DE = 'DE';
}
