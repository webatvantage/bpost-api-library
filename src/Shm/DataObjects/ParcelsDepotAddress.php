<?php

namespace Webatvantage\Bpost\Api\Shm\DataObjects;

use Webatvantage\Bpost\Api\Contracts\XmlNamespace;
use Webatvantage\Bpost\Api\Shm\Enums\ShmNamespace;

/**
 * The address of a parcel locker.
 */
class ParcelsDepotAddress extends Address
{
	protected const string TAG_NAME = 'parcelsDepotAddress';

	protected const ?XmlNamespace TAG_NAMESPACE = ShmNamespace::National;
}
