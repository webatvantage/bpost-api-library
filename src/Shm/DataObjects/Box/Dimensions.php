<?php

namespace Webatvantage\Bpost\Api\Shm\DataObjects\Box;

use Webatvantage\Bpost\Api\Contracts\XmlDeserializable;
use Webatvantage\Bpost\Api\Contracts\XmlNamespace;
use Webatvantage\Bpost\Api\Contracts\XmlSerializable;
use Webatvantage\Bpost\Api\Support\XmlElement;

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
	public function appendTo(XmlElement $parent, ?XmlNamespace $namespace = null): void
	{
		$parent->appendText('height', $this->height, $namespace);
		$parent->appendText('length', $this->length, $namespace);
		$parent->appendText('width', $this->width, $namespace);
	}

	public function toXml(XmlElement $parent, ?XmlNamespace $namespace = null): XmlElement
	{
		$element = $parent->appendElement('dimensions', $namespace);
		$this->appendTo($element, $namespace);

		return $element;
	}

	public static function fromXml(XmlElement $xml): static
	{
		return new static((int)$xml->text('height'), (int)$xml->text('length'), (int)$xml->text('width'));
	}
}
