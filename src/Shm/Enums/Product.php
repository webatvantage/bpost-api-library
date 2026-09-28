<?php

namespace Webatvantage\Bpost\Api\Shm\Enums;

/**
 * Every bpost product name, spelled exactly as the API expects.
 *
 * The casing is inconsistent and load-bearing: "bpack 24h Pro" has a capital P where
 * "bpack 24h business" has a lowercase b. These are bpost's spellings, not ours.
 */
enum Product: string
{
	case Bpack24hPro = 'bpack 24h Pro';
	case Bpack24hBusiness = 'bpack 24h business';
	case BpackBus = 'bpack Bus';
	case BpackPallet = 'bpack Pallet';
	case BpackEasyRetour = 'bpack Easy Retour';
	case BpackXL = 'bpack XL';
	case BpackAtBpost = 'bpack@bpost';
	case BpackClickAndCollect = 'bpack Click & Collect';
	case Bpack247 = 'bpack 24/7';
	case BpackWorldBusiness = 'bpack World Business';
	case BpackWorldExpressPro = 'bpack World Express Pro';
	case BpackEuropeBusiness = 'bpack Europe Business';
	case BpackWorldEasyReturn = 'bpack World Easy Return';
	case BpackAtBpostInternational = 'bpack@bpost international';

	/** The weight ceiling for an ordinary parcel, in grams. */
	public const int MAX_WEIGHT = 30_000;

	/**
	 * Only bpack XL takes dimensions and the fragile option, and it requires the dimensions.
	 */
	public function requiresDimensions(): bool
	{
		return $this === self::BpackXL;
	}

	/**
	 * The heaviest box bpost accepts, in grams, or null where the manual documents no ceiling.
	 */
	public function maxWeight(): ?int
	{
		return match ($this)
		{
			self::BpackXL, self::BpackPallet => null,
			default => self::MAX_WEIGHT,
		};
	}

	public function allowsFragile(): bool
	{
		return $this === self::BpackXL;
	}
}
