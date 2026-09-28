<?php

namespace Webatvantage\Bpost\Api\Shm\DataObjects;

use DOMDocument;
use DOMElement;
use SimpleXMLElement;
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

	public function toXml(DOMDocument $document, ?string $prefix = null): DOMElement
	{
		$line = $document->createElement(Xml::prefixed('orderLine', $prefix));

		Xml::appendText($document, $line, 'text', $this->text, $prefix);
		Xml::appendText($document, $line, 'nbOfItems', $this->numberOfItems, $prefix);

		return $line;
	}

	public static function fromXml(SimpleXMLElement $xml): static
	{
		return new static((string)$xml->text, (int)$xml->nbOfItems);
	}
}
