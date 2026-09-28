<?php

namespace Webatvantage\Bpost\Api\Shm\Enums;

/**
 * What customs should consider the parcel to be.
 *
 * bpost documents a shorter list for the plain international box than for bpack@bpost
 * international, which also accepts RETURNED and GOODS; the union is offered here and the
 * difference is bpost's to enforce.
 */
enum ShipmentType: string
{
	case Sample = 'SAMPLE';
	case Gift = 'GIFT';
	case Goods = 'GOODS';
	case Documents = 'DOCUMENTS';
	case Returned = 'RETURNED';
	case Other = 'OTHER';
}
