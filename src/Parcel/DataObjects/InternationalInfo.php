<?php

namespace Webatvantage\Bpost\Api\Parcel\DataObjects;

use DOMDocument;
use DOMElement;
use Webatvantage\Bpost\Api\Contracts\XmlSerializable;
use Webatvantage\Bpost\Api\Parcel\Enums\ItemCategory;
use Webatvantage\Bpost\Api\Parcel\Enums\NonDeliveryInstruction;
use Webatvantage\Bpost\Api\Parcel\Support\Xml;
use Webatvantage\Bpost\Api\Support\Assert;

/**
 * The customs declaration for an outbound parcel. Required for anything leaving Belgium.
 */
class InternationalInfo implements XmlSerializable
{
	/**
	 * @param float $valueCurrencySender The parcel's value in the currency named below
	 * @param string $currencySender Three-letter currency code
	 */
	public function __construct(
		public private(set) string $parcelContent,
		public private(set) ItemCategory $itemCategory,
		public private(set) NonDeliveryInstruction $nonDeliveryInstructions,
		public private(set) float $valueCurrencySender,
		public private(set) string $currencySender = 'EUR',
	) {
		Assert::maxLength('parcelContent', $parcelContent, 50);
		Assert::maxLength('currencySender', $currencySender, 3);
	}

	public function toXml(DOMDocument $document, ?string $prefix = Xml::PREFIX_ANNOUNCEMENT): DOMElement
	{
		$element = $document->createElement(Xml::prefixed('international', $prefix));

		Xml::appendText($document, $element, 'parcelContent', $this->parcelContent, Xml::PREFIX_COMMON);
		Xml::appendText($document, $element, 'itemCategory', $this->itemCategory->value, Xml::PREFIX_COMMON);
		Xml::appendText(
			$document,
			$element,
			'nonDeliveryInstructions',
			$this->nonDeliveryInstructions->value,
			Xml::PREFIX_COMMON,
		);
		Xml::appendText($document, $element, 'valueCurrencySender', $this->valueCurrencySender, Xml::PREFIX_COMMON);
		Xml::appendText($document, $element, 'currencySender', strtoupper($this->currencySender), Xml::PREFIX_COMMON);

		return $element;
	}
}
