<?php

namespace Webatvantage\Bpost\Api\Parcel\DataObjects\Options;

use DOMDocument;
use DOMElement;
use Webatvantage\Bpost\Api\Contracts\Option;
use Webatvantage\Bpost\Api\Parcel\Support\Xml;
use Webatvantage\Bpost\Api\Support\Assert;

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

	public function toXml(DOMDocument $document, ?string $prefix = Xml::PREFIX_COMMON): DOMElement
	{
		$element = $document->createElement(Xml::prefixed('cashOnDelivery', $prefix));

		Xml::appendText($document, $element, 'amountTotalInEuroCents', $this->amountTotalInEuroCents, $prefix);
		Xml::appendText($document, $element, 'bban', $this->bban, $prefix);
		Xml::appendText($document, $element, 'iban', $this->iban, $prefix);
		Xml::appendText($document, $element, 'bic', $this->bic, $prefix);

		return $element;
	}
}
