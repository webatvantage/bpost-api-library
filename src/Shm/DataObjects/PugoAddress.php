<?php

namespace Webatvantage\Bpost\Api\Shm\DataObjects;

/**
 * The address of a pick-up point, which bpost names differently from a customer's address.
 */
class PugoAddress extends Address
{
	protected const TAG_NAME = 'pugoAddress';

	protected const TAG_PREFIX = null;
}
