<?php

namespace Webatvantage\Bpost\Api\Shm\DataObjects\Box;

use Dom\Element;
use Dom\XMLDocument;
use Webatvantage\Bpost\Api\Contracts\XmlDeserializable;
use Webatvantage\Bpost\Api\Contracts\XmlSerializable;
use Webatvantage\Bpost\Api\Shm\Support\Xml;

/**
 * Parcel dimensions in millimetres.
 *
 * Only bpack XL uses them, and for bpack XL they are mandatory. Written as three sibling elements
 * rather than a wrapper, which is why this is not an element of its own in the request.
 */
class Dimensions implements XmlDeserializable, XmlSerializable
{
	public function __construct(
		public private(set) int $height,
		public private(set) int $length,
		public private(set) int $width,
	) {}

	/**
	 * Append the three elements to the box, since bpost has no wrapper for them.
	 */
	public function appendTo(XMLDocument $document, Element $parent, ?string $prefix = null): void
	{
		Xml::appendText($document, $parent, 'height', $this->height, $prefix);
		Xml::appendText($document, $parent, 'length', $this->length, $prefix);
		Xml::appendText($document, $parent, 'width', $this->width, $prefix);
	}

	public function toXml(XMLDocument $document, ?string $prefix = null): Element
	{
		$element = Xml::element($document, 'dimensions', $prefix);
		$this->appendTo($document, $element, $prefix);

		return $element;
	}

	public static function fromXml(Element $xml): static
	{
		return new static((int)Xml::text($xml, 'height'), (int)Xml::text($xml, 'length'), (int)Xml::text($xml, 'width'));
	}
}
