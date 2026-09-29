<?php

namespace Webatvantage\Bpost\Api\Contracts;

use Webatvantage\Bpost\Api\Support\XmlElement;

/**
 * A data object that can write itself into a bpost request document.
 *
 * Serialisation is explicit rather than reflected from the constructor: bpost validates against an
 * XSD in which element order is significant, and several elements sit in a different namespace
 * depending on where they appear.
 */
interface XmlSerializable
{
	/**
	 * @param XmlElement $parent
	 * @param XmlNamespace|null $namespace The namespace to write this element and its children in
	 *
	 * @return XmlElement
	 */
	public function toXml(XmlElement $parent, ?XmlNamespace $namespace = null): XmlElement;
}
