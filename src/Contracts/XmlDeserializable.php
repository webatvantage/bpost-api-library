<?php

namespace Webatvantage\Bpost\Api\Contracts;

use Webatvantage\Bpost\Api\Support\XmlElement;

/**
 * A data object that can be built from a bpost response document.
 */
interface XmlDeserializable
{
	/**
	 * @param XmlElement $xml
	 *
	 * @return static
	 */
	public static function fromXml(XmlElement $xml): static;
}
