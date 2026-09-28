<?php

namespace Webatvantage\Bpost\Api\Contracts;

use SimpleXMLElement;

/**
 * A data object that can be built from a bpost response document.
 */
interface XmlDeserializable
{
	/**
	 * @param SimpleXMLElement $xml
	 *
	 * @return static
	 */
	public static function fromXml(SimpleXMLElement $xml): static;
}
