<?php

namespace Webatvantage\Bpost\Api\Contracts;

use DOMDocument;
use DOMElement;

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
	 * @param DOMDocument $document
	 * @param string|null $prefix Namespace prefix to write children under, or null for the default namespace
	 *
	 * @return DOMElement
	 */
	public function toXml(DOMDocument $document, ?string $prefix = null): DOMElement;
}
