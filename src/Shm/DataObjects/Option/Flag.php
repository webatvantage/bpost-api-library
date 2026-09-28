<?php

namespace Webatvantage\Bpost\Api\Shm\DataObjects\Option;

use DOMDocument;
use DOMElement;
use Webatvantage\Bpost\Api\Shm\Support\Xml;

/**
 * An option that is simply present or absent, written as an empty element.
 */
abstract class Flag implements Option
{
	abstract protected function tagName(): string;

	public function toXml(DOMDocument $document, ?string $prefix = Xml::PREFIX_COMMON): DOMElement
	{
		return $document->createElement(Xml::prefixed($this->tagName(), $prefix));
	}
}
