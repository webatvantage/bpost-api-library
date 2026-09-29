<?php

namespace Webatvantage\Bpost\Api\Parcel\DataObjects\Options\Flags;

use Dom\Element;
use Dom\XMLDocument;
use Webatvantage\Bpost\Api\Contracts\Flag;
use Webatvantage\Bpost\Api\Parcel\Support\Xml;

/**
 * A flag option on a parcel announcement.
 */
abstract class ParcelFlag extends Flag
{
	protected function element(XMLDocument $document, string $tagName, ?string $prefix): Element
	{
		return Xml::element($document, $tagName, $prefix);
	}
}
