<?php

namespace Webatvantage\Bpost\Api\Parcel\DataObjects\Options;

use DOMDocument;
use DOMElement;
use Webatvantage\Bpost\Api\Parcel\Support\Xml;

abstract class Flag implements Option
{
	abstract protected function tagName(): string;

	public function toXml(DOMDocument $document, ?string $prefix = Xml::PREFIX_COMMON): DOMElement
	{
		return $document->createElement(Xml::prefixed($this->tagName(), $prefix));
	}
}
