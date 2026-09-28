<?php

namespace Webatvantage\Bpost\Api\DataObjects\Options;

use Webatvantage\Bpost\Api\Contracts\XmlSerializable;

/**
 * A value-added service on a parcel.
 *
 * Options are always written under the common prefix, whichever service is being called.
 */
interface Option extends XmlSerializable {}
