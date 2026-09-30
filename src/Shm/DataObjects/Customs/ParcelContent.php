<?php

namespace Webatvantage\Bpost\Api\Shm\DataObjects\Customs;

use Webatvantage\Bpost\Api\Contracts\XmlDeserializable;
use Webatvantage\Bpost\Api\Contracts\XmlNamespace;
use Webatvantage\Bpost\Api\Contracts\XmlSerializable;
use Webatvantage\Bpost\Api\Exceptions\InvalidArgumentException;
use Webatvantage\Bpost\Api\Exceptions\InvalidLengthException;
use Webatvantage\Bpost\Api\Exceptions\InvalidValueException;
use Webatvantage\Bpost\Api\Shm\Enums\ShmNamespace;
use Webatvantage\Bpost\Api\Support\Validate;
use Webatvantage\Bpost\Api\Support\XmlElement;

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
	 *
	 * @throws InvalidLengthException
	 * @throws InvalidValueException
	 */
	public function __construct(
		public private(set) int $numberOfItemType,
		public private(set) int $valueOfItem,
		public private(set) string $itemDescription,
		public private(set) int $nettoWeight,
		public private(set) string $hsTariffCode,
		public private(set) string $originOfGoods,
	) {
		Validate::between('numberOfItemType', $numberOfItemType, 1, 999999);
		Validate::maxLength('itemDescription', $itemDescription, 30);
		Validate::between('nettoWeight', $nettoWeight, 1, 30000);
		Validate::maxLength('hsTariffCode', $hsTariffCode, 9);
		Validate::countryCode('originOfGoods', $originOfGoods);
	}

	/**
	 * @throws InvalidArgumentException
	 */
	public function toXml(XmlElement $parent, ?XmlNamespace $namespace = ShmNamespace::International): XmlElement
	{
		$element = $parent->appendElement('parcelContent', $namespace);

		$element->appendText('numberOfItemType', $this->numberOfItemType, $namespace);
		$element->appendText('valueOfItem', $this->valueOfItem, $namespace);
		$element->appendText('itemDescription', $this->itemDescription, $namespace);
		$element->appendText('nettoWeight', $this->nettoWeight, $namespace);
		$element->appendText('hsTariffCode', $this->hsTariffCode, $namespace);
		$element->appendText('originOfGoods', $this->originOfGoods, $namespace);

		return $element;
	}

	public static function fromXml(XmlElement $xml): static
	{
		return Validate::reading(static fn (): static => new static(
			(int)$xml->text('numberOfItemType'),
			(int)$xml->text('valueOfItem'),
			$xml->text('itemDescription') ?? '',
			(int)$xml->text('nettoWeight'),
			$xml->text('hsTariffCode') ?? '',
			$xml->text('originOfGoods') ?? '',
		));
	}
}
