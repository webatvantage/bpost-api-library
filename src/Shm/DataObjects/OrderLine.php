<?php

namespace Webatvantage\Bpost\Api\Shm\DataObjects;

use Dom\Element;
use Dom\XMLDocument;
use Webatvantage\Bpost\Api\Contracts\XmlDeserializable;
use Webatvantage\Bpost\Api\Contracts\XmlSerializable;
use Webatvantage\Bpost\Api\Shm\Support\Xml;

/**
 * One line of what is in the parcel.
 *
 * Order lines are shown in the Shipping Manager backend to help whoever picks the order; bpost
 * does nothing else with them.
 */
class OrderLine implements XmlDeserializable, XmlSerializable
{
	public function __construct(public private(set) string $text, public private(set) int $numberOfItems) {}

	public function toXml(XMLDocument $document, ?string $prefix = null): Element
	{
		$line = Xml::element($document, 'orderLine', $prefix);

		Xml::appendText($document, $line, 'text', $this->text, $prefix);
		Xml::appendText($document, $line, 'nbOfItems', $this->numberOfItems, $prefix);

		return $line;
	}

	public static function fromXml(Element $xml): static
	{
		return new static(Xml::text($xml, 'text') ?? '', (int)Xml::text($xml, 'nbOfItems'));
	}
}
