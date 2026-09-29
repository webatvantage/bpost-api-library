<?php

namespace Webatvantage\Bpost\Api\Shm\DataObjects\Customs;

use Dom\Element;
use Dom\XMLDocument;
use Webatvantage\Bpost\Api\Contracts\XmlDeserializable;
use Webatvantage\Bpost\Api\Contracts\XmlSerializable;
use Webatvantage\Bpost\Api\Exceptions\InvalidValueException;
use Webatvantage\Bpost\Api\Shm\Enums\Currency;
use Webatvantage\Bpost\Api\Shm\Enums\ParcelReturnInstruction;
use Webatvantage\Bpost\Api\Shm\Enums\ShipmentType;
use Webatvantage\Bpost\Api\Shm\Support\Xml;
use Webatvantage\Bpost\Api\Support\Assert;

/**
 * The customs declaration for an international parcel.
 *
 * Not needed for bpack Europe Business, since nothing is declared inside the EU customs zone.
 */
class CustomsInfo implements XmlDeserializable, XmlSerializable
{
	public private(set) ?Currency $currency = null;

	public private(set) ?float $amtPostagePaidByAddresse = null;

	/**
	 * @param int $parcelValue In cents, so 10 EUR is 1000. Outside the EU this must equal the sum
	 *                         of every parcel content's value
	 */
	public function __construct(
		public private(set) int $parcelValue,
		public private(set) string $contentDescription,
		public private(set) ShipmentType $shipmentType,
		public private(set) ParcelReturnInstruction $parcelReturnInstructions,
		public private(set) bool $privateAddress = false,
	) {
		Assert::maxLength('contentDescription', $contentDescription, 50);
	}

	/**
	 * The currency parcelValue and every content value is expressed in.
	 */
	public function currency(Currency $currency): static
	{
		$this->currency = $currency;

		return $this;
	}

	/**
	 * What the sender paid to ship this parcel. Always in euro, whatever the currency says.
	 */
	public function amtPostagePaidByAddresse(float $amount): static
	{
		if ($amount < 0 || $amount > 999.99)
		{
			throw new InvalidValueException(
				name: 'amtPostagePaidByAddresse',
				value: $amount,
				allowed: ['0 to 999.99'],
			);
		}

		$this->amtPostagePaidByAddresse = $amount;

		return $this;
	}

	public function toXml(XMLDocument $document, ?string $prefix = Xml::PREFIX_INTERNATIONAL): Element
	{
		$element = Xml::element($document, 'customsInfo', $prefix);

		Xml::appendText($document, $element, 'parcelValue', $this->parcelValue, $prefix);
		Xml::appendText($document, $element, 'contentDescription', $this->contentDescription, $prefix);
		Xml::appendText($document, $element, 'shipmentType', $this->shipmentType->value, $prefix);
		Xml::appendText($document, $element, 'parcelReturnInstructions', $this->parcelReturnInstructions->value, $prefix);
		Xml::appendText($document, $element, 'privateAddress', $this->privateAddress ? 'true' : 'false', $prefix);
		Xml::appendText($document, $element, 'currency', $this->currency?->value, $prefix);

		if ($this->amtPostagePaidByAddresse !== null)
		{
			Xml::appendText(
				$document,
				$element,
				'amtPostagePaidByAddresse',
				sprintf('%0.2f', $this->amtPostagePaidByAddresse),
				$prefix,
			);
		}

		return $element;
	}

	public static function fromXml(Element $xml): static
	{
		$info = new static(
			(int)Xml::text($xml, 'parcelValue'),
			Xml::text($xml, 'contentDescription') ?? '',
			Assert::enum('shipmentType', ShipmentType::class, strtoupper(Xml::text($xml, 'shipmentType') ?? '')),
			Assert::enum(
				'parcelReturnInstructions',
				ParcelReturnInstruction::class,
				strtoupper(Xml::text($xml, 'parcelReturnInstructions') ?? ''),
			),
			(Xml::text($xml, 'privateAddress') ?? '') === 'true',
		);

		$currency = Xml::text($xml, 'currency');

		if ($currency !== null)
		{
			$info->currency(Assert::enum('currency', Currency::class, strtoupper($currency)));
		}

		$postagePaid = Xml::text($xml, 'amtPostagePaidByAddresse');

		if ($postagePaid !== null)
		{
			$info->amtPostagePaidByAddresse((float)$postagePaid);
		}

		return $info;
	}
}
