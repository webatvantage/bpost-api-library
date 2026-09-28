<?php

namespace Webatvantage\Bpost\Api\Parcel\Enums;

/**
 * What bpost should do with an undeliverable outbound parcel.
 */
enum NonDeliveryInstruction: string
{
	/** Return to sender. */
	case ReturnToSender = 'RTS';

	/** Return by air. */
	case ReturnByAir = 'RTA';

	/** Destroy it. */
	case Abandoned = 'ABANDONED';
}
