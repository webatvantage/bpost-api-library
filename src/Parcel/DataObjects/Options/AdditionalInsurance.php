<?php

namespace Webatvantage\Bpost\Api\Parcel\DataObjects\Options;

use Webatvantage\Bpost\Api\Contracts\Option;
use Webatvantage\Bpost\Api\Contracts\XmlNamespace;
use Webatvantage\Bpost\Api\Parcel\Enums\InsuranceAmount;
use Webatvantage\Bpost\Api\Parcel\Enums\ParcelNamespace;
use Webatvantage\Bpost\Api\Support\XmlElement;

/**
 * Warranty above the basic band.
 *
 * The band goes in a maxAmount element rather than an attribute, which is how this service differs
 * from the Shipping Manager's equivalent.
 */
class AdditionalInsurance implements Option
{
	public function __construct(public private(set) InsuranceAmount $maxAmount) {}

	public function toXml(XmlElement $parent, ?XmlNamespace $namespace = ParcelNamespace::Common): XmlElement
	{
		$element = $parent->appendElement('additionalInsurance', $namespace);

		$element->appendText('maxAmount', $this->maxAmount->value, $namespace);

		return $element;
	}
}
