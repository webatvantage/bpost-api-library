<?php

namespace Webatvantage\Bpost\Api\Parcel\DataObjects\Options;

use DOMDocument;
use DOMElement;
use Webatvantage\Bpost\Api\Parcel\Enums\InsuranceAmount;
use Webatvantage\Bpost\Api\Parcel\Support\Xml;

/**
 * Warranty above the basic band.
 *
 * The band goes in a maxAmount element rather than an attribute, which is how this service differs
 * from the Shipping Manager's equivalent.
 */
class AdditionalInsurance implements Option
{
	public function __construct(public private(set) InsuranceAmount $maxAmount) {}

	public function toXml(DOMDocument $document, ?string $prefix = Xml::PREFIX_COMMON): DOMElement
	{
		$element = $document->createElement(Xml::prefixed('additionalInsurance', $prefix));

		Xml::appendText($document, $element, 'maxAmount', $this->maxAmount->value, $prefix);

		return $element;
	}
}
