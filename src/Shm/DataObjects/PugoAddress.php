<?php

namespace Webatvantage\Bpost\Api\Shm\DataObjects;

use Webatvantage\Bpost\Api\Contracts\XmlNamespace;
use Webatvantage\Bpost\Api\Shm\Enums\ShmNamespace;

/**
 * The address of a pick-up point, which bpost names differently from a customer's address.
 */
class PugoAddress extends Address
{
	protected const string TAG_NAME = 'pugoAddress';

	protected const ?XmlNamespace TAG_NAMESPACE = ShmNamespace::National;
}
