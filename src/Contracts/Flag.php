<?php

namespace Webatvantage\Bpost\Api\Contracts;

use Webatvantage\Bpost\Api\Support\XmlElement;

/**
 * An option that is nothing but its own presence, written as an empty element.
 */
abstract class Flag implements Option
{
	abstract protected function tagName(): string;

	public function toXml(XmlElement $parent, ?XmlNamespace $namespace = null): XmlElement
	{
		return $parent->appendElement($this->tagName(), $namespace);
	}
}
