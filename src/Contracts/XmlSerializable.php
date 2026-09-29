<?php

namespace Webatvantage\Bpost\Api\Contracts;

use Dom\Element;
use Dom\XMLDocument;

/**
 * A data object that can write itself into a bpost request document.
 *
 * Serialisation is explicit rather than reflected from the constructor: bpost validates against an
 * XSD in which element order is significant, and several elements are namespace-prefixed
 * differently depending on where they appear.
 */
interface XmlSerializable
{
	/**
	 * @param XMLDocument $document
	 * @param string|null $prefix Namespace prefix to write children under, or null for the default namespace
	 *
	 * @return Element
	 */
	public function toXml(XMLDocument $document, ?string $prefix = null): Element;
}
