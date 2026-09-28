<?php

namespace Webatvantage\Bpost\Api\Parcel\DataObjects\Options;

use Webatvantage\Bpost\Api\Contracts\XmlSerializable;

/**
 * A value-added service on an announced parcel.
 *
 * The announcement service spells these differently from the Shipping Manager: signature rather
 * than signed, insurance rather than insured, cashOnDelivery rather than cod.
 */
interface Option extends XmlSerializable {}
