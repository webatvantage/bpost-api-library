<?php

namespace Webatvantage\Bpost\Api\Shm\DataObjects\Option;

use DOMDocument;
use DOMElement;
use SimpleXMLElement;
use Webatvantage\Bpost\Api\Contracts\XmlDeserializable;
use Webatvantage\Bpost\Api\Shm\Support\Xml;

/**
 * Collect payment on delivery, into an IBAN account.
 *
 * The amount is in euro cents, and it is printed on the label as its own barcode so the parcel can
 * be checked for payment. This option includes a signature.
 */
class CashOnDelivery implements Option, XmlDeserializable
{
	/**
	 * @param int $amount Euro cents, so 12.51 EUR is 1251
	 */
	public function __construct(
		public private(set) int $amount,
		public private(set) string $iban,
		public private(set) string $bic,
	) {}

	public function toXml(DOMDocument $document, ?string $prefix = Xml::PREFIX_COMMON): DOMElement
	{
		$cod = $document->createElement(Xml::prefixed('cod', $prefix));

		Xml::appendText($document, $cod, 'codAmount', $this->amount, $prefix);
		Xml::appendText($document, $cod, 'iban', $this->iban, $prefix);
		Xml::appendText($document, $cod, 'bic', $this->bic, $prefix);

		return $cod;
	}

	public static function fromXml(SimpleXMLElement $xml): static
	{
		$children = Xml::readChildren($xml, Xml::READ_COMMON);

		return new static(
			(int)$children->codAmount,
			(string)$children->iban,
			(string)$children->bic,
		);
	}
}
