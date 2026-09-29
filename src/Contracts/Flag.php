<?php

namespace Webatvantage\Bpost\Api\Contracts;

use Dom\Element;
use Dom\XMLDocument;
use Webatvantage\Bpost\Api\Support\Xml;

/**
 * An option that is nothing but its own presence, written as an empty element.
 *
 * The common prefix maps to a different namespace per service, so the element itself is created by
 * a subclass that knows which support class to reach for.
 */
abstract class Flag implements Option
{
	abstract protected function tagName(): string;

	abstract protected function element(XMLDocument $document, string $tagName, ?string $prefix): Element;

	public function toXml(XMLDocument $document, ?string $prefix = Xml::PREFIX_COMMON): Element
	{
		return $this->element($document, $this->tagName(), $prefix);
	}
}
