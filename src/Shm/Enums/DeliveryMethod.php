<?php

namespace Webatvantage\Bpost\Api\Shm\Enums;

/**
 * The delivery methods a product configuration groups its products under.
 *
 * bpost's own casing, which is not consistent between them.
 */
enum DeliveryMethod: string
{
	case HomeOrOffice = 'home or office';
	case PickupPoint = 'pick-up point';
	case ParcelLocker = 'Parcel locker';
	case ClickAndCollect = 'Click & Collect';
}
