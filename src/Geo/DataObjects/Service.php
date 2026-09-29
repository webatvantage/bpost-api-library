<?php

namespace Webatvantage\Bpost\Api\Geo\DataObjects;

use Dom\Element;
use Webatvantage\Bpost\Api\Contracts\XmlDeserializable;
use Webatvantage\Bpost\Api\Support\Xml;

class Service implements XmlDeserializable
{
	public function __construct(
		public readonly string $name,
		public readonly ?string $category = null,
		public readonly ?string $flag = null,
	) {}

	public static function fromXml(Element $xml): static
	{
		return new static(
			trim($xml->textContent),
			Xml::attribute($xml, 'category'),
			Xml::attribute($xml, 'flag'),
		);
	}
}
