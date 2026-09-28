<?php

namespace Webatvantage\Bpost\Api\Parcel\DataObjects;

use DOMDocument;
use DOMElement;
use SimpleXMLElement;
use Webatvantage\Bpost\Api\Contracts\XmlDeserializable;
use Webatvantage\Bpost\Api\Contracts\XmlSerializable;
use Webatvantage\Bpost\Api\Parcel\Support\Xml;
use Webatvantage\Bpost\Api\Support\Assert;

/**
 * Parcel dimensions in millimetres. Mandatory for bpack XL.
 */
class Dimensions implements XmlDeserializable, XmlSerializable
{
	public function __construct(
		public private(set) int $widthInMm,
		public private(set) int $heightInMm,
		public private(set) int $lengthInMm,
	) {
		Assert::between('widthInMm', $widthInMm, 1, 9999);
		Assert::between('heightInMm', $heightInMm, 1, 9999);
		Assert::between('lengthInMm', $lengthInMm, 1, 9999);
	}

	public function toXml(DOMDocument $document, ?string $prefix = Xml::PREFIX_ANNOUNCEMENT): DOMElement
	{
		$element = $document->createElement(Xml::prefixed('dimensions', $prefix));

		Xml::appendText($document, $element, 'widthInMm', $this->widthInMm, Xml::PREFIX_COMMON);
		Xml::appendText($document, $element, 'heightInMm', $this->heightInMm, Xml::PREFIX_COMMON);
		Xml::appendText($document, $element, 'lengthInMm', $this->lengthInMm, Xml::PREFIX_COMMON);

		return $element;
	}

	public static function fromXml(SimpleXMLElement $xml): static
	{
		return new static((int)$xml->widthInMm, (int)$xml->heightInMm, (int)$xml->lengthInMm);
	}
}
