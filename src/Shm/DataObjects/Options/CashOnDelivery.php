<?php

namespace Webatvantage\Bpost\Api\Shm\DataObjects\Options;

use Webatvantage\Bpost\Api\Contracts\Option;
use Webatvantage\Bpost\Api\Contracts\XmlDeserializable;
use Webatvantage\Bpost\Api\Contracts\XmlNamespace;
use Webatvantage\Bpost\Api\Exceptions\InvalidArgumentException;
use Webatvantage\Bpost\Api\Shm\Enums\ShmNamespace;
use Webatvantage\Bpost\Api\Support\XmlElement;

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

	/**
	 * @throws InvalidArgumentException
	 */
	public function toXml(XmlElement $parent, ?XmlNamespace $namespace = ShmNamespace::Common): XmlElement
	{
		$cod = $parent->appendElement('cod', $namespace);

		$cod->appendText('codAmount', $this->amount, $namespace);
		$cod->appendText('iban', $this->iban, $namespace);
		$cod->appendText('bic', $this->bic, $namespace);

		return $cod;
	}

	public static function fromXml(XmlElement $xml): static
	{
		return new static(
			(int)$xml->text('codAmount'),
			$xml->text('iban') ?? '',
			$xml->text('bic') ?? '',
		);
	}
}
