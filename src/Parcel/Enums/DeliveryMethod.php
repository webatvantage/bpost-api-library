<?php

namespace Webatvantage\Bpost\Api\Parcel\Enums;

/**
 * Where the parcel is going. The case value is the empty element written inside deliveryMethod.
 */
enum DeliveryMethod: string
{
	/** An address, national or international. */
	case AtHome = 'atHome';

	/** A national pick-up point. */
	case AtShop = 'atShop';

	/** A national parcel locker. */
	case At247 = 'at24-7';

	/** An international pick-up point or locker. */
	case AtInternationalShop = 'atIntlShop';
}
