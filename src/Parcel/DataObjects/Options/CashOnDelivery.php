<?php

namespace Webatvantage\Bpost\Api\Parcel\DataObjects\Options;

use Webatvantage\Bpost\Api\Contracts\Option;
use Webatvantage\Bpost\Api\Contracts\XmlNamespace;
use Webatvantage\Bpost\Api\Parcel\Enums\ParcelNamespace;
use Webatvantage\Bpost\Api\Support\Assert;
use Webatvantage\Bpost\Api\Support\XmlElement;

/**
 * Collect payment on delivery.
 *
 * bpost caps this at 7 500 EUR and will not accept less than 2,50 EUR, and only Belgian IBANs.
 */
class CashOnDelivery implements Option
{
	public const int MIN_AMOUNT = 250;

	public const int MAX_AMOUNT = 750_000;

	/**
	 * @param int $amountTotalInEuroCents Euro cents, so 12.51 EUR is 1251
	 */
	public function __construct(
		public private(set) int $amountTotalInEuroCents,
		public private(set) ?string $iban = null,
		public private(set) ?string $bic = null,
		public private(set) ?string $bban = null,
	) {
		Assert::between('amountTotalInEuroCents', $amountTotalInEuroCents, self::MIN_AMOUNT, self::MAX_AMOUNT);
	}

	public function toXml(XmlElement $parent, ?XmlNamespace $namespace = ParcelNamespace::Common): XmlElement
	{
		$element = $parent->appendElement('cashOnDelivery', $namespace);

		$element->appendText('amountTotalInEuroCents', $this->amountTotalInEuroCents, $namespace);
		$element->appendText('bban', $this->bban, $namespace);
		$element->appendText('iban', $this->iban, $namespace);
		$element->appendText('bic', $this->bic, $namespace);

		return $element;
	}
}
