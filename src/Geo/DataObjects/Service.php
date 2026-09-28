<?php

namespace Webatvantage\Bpost\Api\Geo\DataObjects;

use SimpleXMLElement;
use Webatvantage\Bpost\Api\Contracts\XmlDeserializable;

final class Service implements XmlDeserializable
{
	public function __construct(
		public readonly string $name,
		public readonly ?string $category = null,
		public readonly ?string $flag = null,
	) {}

	public static function fromXml(SimpleXMLElement $xml): static
	{
		return new static(
			trim((string)$xml),
			isset($xml['category']) ? (string)$xml['category'] : null,
			isset($xml['flag']) ? (string)$xml['flag'] : null,
		);
	}
}
