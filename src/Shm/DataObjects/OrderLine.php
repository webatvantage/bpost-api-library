<?php

namespace Webatvantage\Bpost\Api\Shm\DataObjects;

use Webatvantage\Bpost\Api\Contracts\XmlDeserializable;
use Webatvantage\Bpost\Api\Contracts\XmlNamespace;
use Webatvantage\Bpost\Api\Contracts\XmlSerializable;
use Webatvantage\Bpost\Api\Support\XmlElement;

/**
 * One line of what is in the parcel.
 *
 * Order lines are shown in the Shipping Manager backend to help whoever picks the order; bpost
 * does nothing else with them.
 */
class OrderLine implements XmlDeserializable, XmlSerializable
{
	public function __construct(public private(set) string $text, public private(set) int $numberOfItems) {}

	public function toXml(XmlElement $parent, ?XmlNamespace $namespace = null): XmlElement
	{
		$line = $parent->appendElement('orderLine', $namespace);

		$line->appendText('text', $this->text, $namespace);
		$line->appendText('nbOfItems', $this->numberOfItems, $namespace);

		return $line;
	}

	public static function fromXml(XmlElement $xml): static
	{
		return new static($xml->text('text') ?? '', (int)$xml->text('nbOfItems'));
	}
}
