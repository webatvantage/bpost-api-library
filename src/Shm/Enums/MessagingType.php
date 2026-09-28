<?php

namespace Webatvantage\Bpost\Api\Shm\Enums;

/**
 * The notification messages bpost can send. The case value is also the XML element name.
 */
enum MessagingType: string
{
	/** The parcel has been delivered. */
	case InfoDistributed = 'infoDistributed';

	/** The parcel will be delivered the next business day. */
	case InfoNextDay = 'infoNextDay';

	/** The parcel is waiting at the post office. */
	case InfoReminder = 'infoReminder';

	/** The parcel is available at the pick-up point. Only for bpack@bpost. */
	case KeepMeInformed = 'keepMeInformed';
}
