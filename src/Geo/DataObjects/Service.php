<?php

namespace Webatvantage\Bpost\Api\Geo\DataObjects;

use Webatvantage\Bpost\Api\Contracts\XmlDeserializable;
use Webatvantage\Bpost\Api\Support\XmlElement;

class Service implements XmlDeserializable
{
	public function __construct(
		public readonly string $name,
		public readonly ?string $category = null,
		public readonly ?string $flag = null,
	) {}

	public static function fromXml(XmlElement $xml): static
	{
		return new static(
			name: trim($xml->textContent),
			category: $xml->attribute('category'),
			flag: $xml->attribute('flag'),
		);
	}
}
