<?php

namespace Webatvantage\Bpost\Api\Shm\DataObjects\Option;

use DOMDocument;
use DOMElement;
use SimpleXMLElement;
use Webatvantage\Bpost\Api\Contracts\XmlDeserializable;
use Webatvantage\Bpost\Api\Shm\Enums\InsuranceAmount;
use Webatvantage\Bpost\Api\Shm\Enums\InsuranceType;
use Webatvantage\Bpost\Api\Shm\Support\Xml;

/**
 * Warranty, which bpost still spells "insurance" in the XML.
 *
 * Basic covers up to 500 EUR and carries no amount; additional names a band. This option includes
 * a signature.
 */
class Insured implements Option, XmlDeserializable
{
	private function __construct(public private(set) InsuranceType $type, public private(set) ?InsuranceAmount $amount = null) {}

	/** Up to 500 EUR. */
	public static function basic(): static
	{
		return new static(InsuranceType::Basic);
	}

	/** Up to 2 500 or 5 000 EUR. */
	public static function additional(InsuranceAmount $amount): static
	{
		return new static(InsuranceType::Additional, $amount);
	}

	public function toXml(DOMDocument $document, ?string $prefix = Xml::PREFIX_COMMON): DOMElement
	{
		$insured = $document->createElement(Xml::prefixed('insured', $prefix));
		$band = $document->createElement(Xml::prefixed($this->type->value, $prefix));

		if ($this->amount !== null)
		{
			$band->setAttribute('value', (string)$this->amount->value);
		}

		$insured->appendChild($band);

		return $insured;
	}

	public static function fromXml(SimpleXMLElement $xml): static
	{
		$children = Xml::readChildren($xml, Xml::READ_COMMON);

		if (isset($children->additionalInsurance))
		{
			$value = (int)Xml::attribute($children->additionalInsurance, 'value');
			$amount = InsuranceAmount::tryFrom($value);

			// bpost answers basic warranty as additionalInsurance value="1" on some orders.
			return $amount === null ? static::basic() : static::additional($amount);
		}

		return static::basic();
	}
}
