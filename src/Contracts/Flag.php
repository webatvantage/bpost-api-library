<?php

namespace Webatvantage\Bpost\Api\Contracts;

use DOMDocument;
use DOMElement;
use Webatvantage\Bpost\Api\Support\Xml;

/**
 * An option that is nothing but its own presence, written as an empty element.
 */
abstract class Flag implements Option
{
	abstract protected function tagName(): string;

	public function toXml(DOMDocument $document, ?string $prefix = Xml::PREFIX_COMMON): DOMElement
	{
		return $document->createElement(Xml::prefixed($this->tagName(), $prefix));
	}
}
