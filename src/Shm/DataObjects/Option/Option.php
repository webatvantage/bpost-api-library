<?php

namespace Webatvantage\Bpost\Api\Shm\DataObjects\Option;

use Webatvantage\Bpost\Api\Contracts\XmlSerializable;

/**
 * A value-added service on a box.
 *
 * Every option is written under the common namespace regardless of whether the box around it is
 * national or international.
 */
interface Option extends XmlSerializable {}
