<?php

namespace Webatvantage\Bpost\Api\Shm\DataObjects\Customs;

use Dom\Element;
use Dom\XMLDocument;
use Webatvantage\Bpost\Api\Contracts\XmlDeserializable;
use Webatvantage\Bpost\Api\Contracts\XmlSerializable;
use Webatvantage\Bpost\Api\Shm\Support\Xml;
use Webatvantage\Bpost\Api\Support\Assert;

/**
 * One kind of item in a parcel leaving the EU customs zone.
 *
 * This is the Electronic Advance Data customs require; a wrong or missing set delays the parcel or
 * sends it back. Every figure is for the whole quantity, not per item.
 */
class ParcelContent implements XmlDeserializable, XmlSerializable
{
	/**
	 * @param int $numberOfItemType How many of this item
	 * @param int $valueOfItem Value of all of them, in cents of the parcel's currency
	 * @param string $itemDescription What they are, at most 30 characters
	 * @param int $nettoWeight Weight of all of them in grams, 1 to 30000
	 * @param string $hsTariffCode Harmonised System code, at most 9 digits
	 * @param string $originOfGoods Two-letter country code where they were made
	 */
	public function __construct(
		public private(set) int $numberOfItemType,
		public private(set) int $valueOfItem,
		public private(set) string $itemDescription,
		public private(set) int $nettoWeight,
		public private(set) string $hsTariffCode,
		public private(set) string $originOfGoods,
	) {
		Assert::between('numberOfItemType', $numberOfItemType, 1, 999999);
		Assert::maxLength('itemDescription', $itemDescription, 30);
		Assert::between('nettoWeight', $nettoWeight, 1, 30000);
		Assert::maxLength('hsTariffCode', $hsTariffCode, 9);
		Assert::countryCode('originOfGoods', $originOfGoods);
	}

	public function toXml(XMLDocument $document, ?string $prefix = Xml::PREFIX_INTERNATIONAL): Element
	{
		$element = Xml::element($document, 'parcelContent', $prefix);

		Xml::appendText($document, $element, 'numberOfItemType', $this->numberOfItemType, $prefix);
		Xml::appendText($document, $element, 'valueOfItem', $this->valueOfItem, $prefix);
		Xml::appendText($document, $element, 'itemDescription', $this->itemDescription, $prefix);
		Xml::appendText($document, $element, 'nettoWeight', $this->nettoWeight, $prefix);
		Xml::appendText($document, $element, 'hsTariffCode', $this->hsTariffCode, $prefix);
		Xml::appendText($document, $element, 'originOfGoods', $this->originOfGoods, $prefix);

		return $element;
	}

	public static function fromXml(Element $xml): static
	{
		return new static(
			(int)Xml::text($xml, 'numberOfItemType'),
			(int)Xml::text($xml, 'valueOfItem'),
			Xml::text($xml, 'itemDescription') ?? '',
			(int)Xml::text($xml, 'nettoWeight'),
			Xml::text($xml, 'hsTariffCode') ?? '',
			Xml::text($xml, 'originOfGoods') ?? '',
		);
	}
}
