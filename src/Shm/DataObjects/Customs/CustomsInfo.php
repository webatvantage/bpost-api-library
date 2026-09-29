<?php

namespace Webatvantage\Bpost\Api\Shm\DataObjects\Customs;

use Webatvantage\Bpost\Api\Contracts\XmlDeserializable;
use Webatvantage\Bpost\Api\Contracts\XmlNamespace;
use Webatvantage\Bpost\Api\Contracts\XmlSerializable;
use Webatvantage\Bpost\Api\Exceptions\InvalidValueException;
use Webatvantage\Bpost\Api\Shm\Enums\Currency;
use Webatvantage\Bpost\Api\Shm\Enums\ParcelReturnInstruction;
use Webatvantage\Bpost\Api\Shm\Enums\ShipmentType;
use Webatvantage\Bpost\Api\Shm\Enums\ShmNamespace;
use Webatvantage\Bpost\Api\Support\Assert;
use Webatvantage\Bpost\Api\Support\XmlElement;

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

	public function toXml(XmlElement $parent, ?XmlNamespace $namespace = ShmNamespace::International): XmlElement
	{
		$element = $parent->appendElement('customsInfo', $namespace);

		$element->appendText('parcelValue', $this->parcelValue, $namespace);
		$element->appendText('contentDescription', $this->contentDescription, $namespace);
		$element->appendText('shipmentType', $this->shipmentType->value, $namespace);
		$element->appendText('parcelReturnInstructions', $this->parcelReturnInstructions->value, $namespace);
		$element->appendText('privateAddress', $this->privateAddress ? 'true' : 'false', $namespace);
		$element->appendText('currency', $this->currency?->value, $namespace);

		if ($this->amtPostagePaidByAddresse !== null)
		{
			$element->appendText(
				'amtPostagePaidByAddresse',
				sprintf('%0.2f', $this->amtPostagePaidByAddresse),
				$namespace,
			);
		}

		return $element;
	}

	public static function fromXml(XmlElement $xml): static
	{
		$info = new static(
			(int)$xml->text('parcelValue'),
			$xml->text('contentDescription') ?? '',
			Assert::enum('shipmentType', ShipmentType::class, strtoupper($xml->text('shipmentType') ?? '')),
			Assert::enum(
				'parcelReturnInstructions',
				ParcelReturnInstruction::class,
				strtoupper($xml->text('parcelReturnInstructions') ?? ''),
			),
			($xml->text('privateAddress') ?? '') === 'true',
		);

		$currency = $xml->text('currency');

		if ($currency !== null)
		{
			$info->currency(Assert::enum('currency', Currency::class, strtoupper($currency)));
		}

		$postagePaid = $xml->text('amtPostagePaidByAddresse');

		if ($postagePaid !== null)
		{
			$info->amtPostagePaidByAddresse((float)$postagePaid);
		}

		return $info;
	}
}
