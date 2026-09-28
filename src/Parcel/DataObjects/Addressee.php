<?php

namespace Webatvantage\Bpost\Api\Parcel\DataObjects;

/**
 * The receiver as a tracking response names it, which is not what a request calls it.
 */
class Addressee extends Party
{
	protected const string TAG_NAME = 'addressee';
}
