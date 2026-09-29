<?php

namespace Webatvantage\Bpost\Api\Parcel\DataObjects;

use Webatvantage\Bpost\Api\Contracts\XmlNamespace;
use Webatvantage\Bpost\Api\Contracts\XmlSerializable;
use Webatvantage\Bpost\Api\Exceptions\InvalidArgumentException;
use Webatvantage\Bpost\Api\Exceptions\InvalidLengthException;
use Webatvantage\Bpost\Api\Parcel\Enums\ItemCategory;
use Webatvantage\Bpost\Api\Parcel\Enums\NonDeliveryInstruction;
use Webatvantage\Bpost\Api\Parcel\Enums\ParcelNamespace;
use Webatvantage\Bpost\Api\Support\Validate;
use Webatvantage\Bpost\Api\Support\XmlElement;

/**
 * The customs declaration for an outbound parcel. Required for anything leaving Belgium.
 */
class InternationalInfo implements XmlSerializable
{
	/**
	 * @param float $valueCurrencySender The parcel's value in the currency named below
	 * @param string $currencySender Three-letter currency code
	 *
	 * @throws InvalidLengthException
	 */
	public function __construct(
		public private(set) string $parcelContent,
		public private(set) ItemCategory $itemCategory,
		public private(set) NonDeliveryInstruction $nonDeliveryInstructions,
		public private(set) float $valueCurrencySender,
		public private(set) string $currencySender = 'EUR',
	) {
		Validate::maxLength('parcelContent', $parcelContent, 50);
		Validate::maxLength('currencySender', $currencySender, 3);
	}

	/**
	 * @throws InvalidArgumentException
	 */
	public function toXml(XmlElement $parent, ?XmlNamespace $namespace = ParcelNamespace::Announcement): XmlElement
	{
		$element = $parent->appendElement('international', $namespace);

		$element->appendText('parcelContent', $this->parcelContent, ParcelNamespace::Common);
		$element->appendText('itemCategory', $this->itemCategory->value, ParcelNamespace::Common);
		$element->appendText(
			'nonDeliveryInstructions',
			$this->nonDeliveryInstructions->value,
			ParcelNamespace::Common,
		);
		$element->appendText('valueCurrencySender', $this->valueCurrencySender, ParcelNamespace::Common);
		$element->appendText('currencySender', strtoupper($this->currencySender), ParcelNamespace::Common);

		return $element;
	}
}
