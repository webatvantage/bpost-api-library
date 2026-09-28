<?php

namespace Webatvantage\Bpost\Api\Shm\Enums;

/**
 * What bpost should do with an undeliverable parcel.
 */
enum ParcelReturnInstruction: string
{
	/** Return to sender by road. */
	case ReturnToSender = 'RTS';

	/** Return to sender by air. */
	case ReturnByAir = 'RTA';

	/** Destroy it. */
	case Abandoned = 'ABANDONED';
}
