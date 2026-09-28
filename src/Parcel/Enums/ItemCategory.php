<?php

namespace Webatvantage\Bpost\Api\Parcel\Enums;

/**
 * What customs should consider an outbound parcel to be.
 *
 * Note bpost spells one of these with a space, unlike the Shipping Manager's RETURNED.
 */
enum ItemCategory: string
{
	case Gift = 'GIFT';
	case Documents = 'DOCUMENTS';
	case Sample = 'SAMPLE';
	case ReturnedGoods = 'RETURNED GOODS';
	case Goods = 'GOODS';
	case Other = 'OTHER';
}
