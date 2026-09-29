<?php

namespace Webatvantage\Bpost\Api\Shm\DataObjects\Options\Flags;

use Dom\Element;
use Dom\XMLDocument;
use Webatvantage\Bpost\Api\Contracts\Flag;
use Webatvantage\Bpost\Api\Shm\Support\Xml;

/**
 * A flag option on a Shipping Manager box.
 */
abstract class ShmFlag extends Flag
{
	protected function element(XMLDocument $document, string $tagName, ?string $prefix): Element
	{
		return Xml::element($document, $tagName, $prefix);
	}
}
