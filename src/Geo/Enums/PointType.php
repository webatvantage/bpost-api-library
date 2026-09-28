<?php

namespace Webatvantage\Bpost\Api\Geo\Enums;

/**
 * The kinds of pick-up point the Geolocator knows about.
 *
 * bpost combines them by summing the codes, so asking for post offices, post points and parcel
 * points is `Type=19`. Use self::mask() rather than adding the values by hand.
 */
enum PointType: int
{
	case PostOffice = 1;
	case PostPoint = 2;
	case ParcelLocker = 4;
	case ClickAndCollectShop = 8;
	case ParcelPoint = 16;

	/**
	 * Combine several types into the single integer bpost expects.
	 */
	public static function mask(self ...$types): int
	{
		$mask = 0;

		foreach ($types as $type)
		{
			$mask |= $type->value;
		}

		return $mask;
	}

	/**
	 * Split a combined Type value back into its parts.
	 *
	 * @return array<self>
	 */
	public static function fromMask(int $mask): array
	{
		$types = [];

		foreach (self::cases() as $case)
		{
			if (($mask & $case->value) === $case->value)
			{
				$types[] = $case;
			}
		}

		return $types;
	}
}
