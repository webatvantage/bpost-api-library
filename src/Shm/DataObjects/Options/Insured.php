<?php

namespace Webatvantage\Bpost\Api\Shm\DataObjects\Options;

use Webatvantage\Bpost\Api\Contracts\Option;
use Webatvantage\Bpost\Api\Contracts\XmlDeserializable;
use Webatvantage\Bpost\Api\Contracts\XmlNamespace;
use Webatvantage\Bpost\Api\Exceptions\InvalidArgumentException;
use Webatvantage\Bpost\Api\Shm\Enums\InsuranceAmount;
use Webatvantage\Bpost\Api\Shm\Enums\InsuranceType;
use Webatvantage\Bpost\Api\Shm\Enums\ShmNamespace;
use Webatvantage\Bpost\Api\Support\XmlElement;

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

	/**
	 * @throws InvalidArgumentException
	 */
	public function toXml(XmlElement $parent, ?XmlNamespace $namespace = ShmNamespace::Common): XmlElement
	{
		$insured = $parent->appendElement('insured', $namespace);
		$band = $insured->appendElement($this->type->value, $namespace);

		if ($this->amount !== null)
		{
			$band->setAttribute('value', (string)$this->amount->value);
		}

		return $insured;
	}

	public static function fromXml(XmlElement $xml): static
	{
		$additional = $xml->child('additionalInsurance');

		if ($additional !== null)
		{
			$value = (int)$additional->attribute('value');
			$amount = InsuranceAmount::tryFrom($value);

			// bpost answers basic warranty as additionalInsurance value="1" on some orders.
			return $amount === null ? static::basic() : static::additional($amount);
		}

		return static::basic();
	}
}
