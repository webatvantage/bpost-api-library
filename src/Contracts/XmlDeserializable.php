<?php

namespace Webatvantage\Bpost\Api\Contracts;

use Dom\Element;

/**
 * A data object that can be built from a bpost response document.
 */
interface XmlDeserializable
{
	/**
	 * @param Element $xml
	 *
	 * @return static
	 */
	public static function fromXml(Element $xml): static;
}
