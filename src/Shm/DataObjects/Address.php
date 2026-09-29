<?php

namespace Webatvantage\Bpost\Api\Shm\DataObjects;

use Webatvantage\Bpost\Api\Contracts\XmlDeserializable;
use Webatvantage\Bpost\Api\Contracts\XmlNamespace;
use Webatvantage\Bpost\Api\Contracts\XmlSerializable;
use Webatvantage\Bpost\Api\Exceptions\InvalidLengthException;
use Webatvantage\Bpost\Api\Exceptions\InvalidValueException;
use Webatvantage\Bpost\Api\Shm\Enums\ShmNamespace;
use Webatvantage\Bpost\Api\Support\Validate;
use Webatvantage\Bpost\Api\Support\XmlElement;

/**
 * A postal address.
 *
 * The element itself is named by the subclass — a pick-up point's address is <pugoAddress>, a
 * locker's is <parcelsDepotAddress> — but the children are always in the common namespace.
 */
class Address implements XmlDeserializable, XmlSerializable
{
	protected const string TAG_NAME = 'address';

	protected const ?XmlNamespace TAG_NAMESPACE = ShmNamespace::Common;

	public private(set) ?string $streetName = null;

	public private(set) ?string $number = null;

	public private(set) ?string $box = null;

	public private(set) ?string $postalCode = null;

	public private(set) ?string $locality = null;

	public private(set) string $countryCode = 'BE';

	/**
	 * @throws InvalidLengthException
	 */
	public function streetName(string $streetName): static
	{
		$this->streetName = Validate::maxLength('streetName', $streetName, 40);

		return $this;
	}

	/**
	 * @throws InvalidLengthException
	 */
	public function number(string|int $number): static
	{
		$this->number = Validate::maxLength('number', (string)$number, 8);

		return $this;
	}

	/**
	 * bpost documents a maximum of 8 here, but accepts 9 in practice and the library has relied on
	 * that since 3.7; see commits a7bdb27 and a97d061.
	 *
	 * @throws InvalidLengthException
	 */
	public function box(string|int $box): static
	{
		$this->box = Validate::maxLength('box', (string)$box, 9);

		return $this;
	}

	/**
	 * @throws InvalidLengthException
	 */
	public function postalCode(string|int $postalCode): static
	{
		$this->postalCode = Validate::maxLength('postalCode', (string)$postalCode, 40);

		return $this;
	}

	/**
	 * @throws InvalidLengthException
	 */
	public function locality(string $locality): static
	{
		$this->locality = Validate::maxLength('locality', $locality, 40);

		return $this;
	}

	/**
	 * @throws InvalidValueException
	 */
	public function countryCode(string $countryCode): static
	{
		$this->countryCode = Validate::countryCode('countryCode', $countryCode);

		return $this;
	}

	public function toXml(XmlElement $parent, ?XmlNamespace $namespace = null): XmlElement
	{
		$address = $parent->appendElement(static::TAG_NAME, static::TAG_NAMESPACE);

		$address->appendText('streetName', $this->streetName, ShmNamespace::Common);
		$address->appendText('number', $this->number, ShmNamespace::Common);
		$address->appendText('box', $this->box, ShmNamespace::Common);
		$address->appendText('postalCode', $this->postalCode, ShmNamespace::Common);
		$address->appendText('locality', $this->locality, ShmNamespace::Common);
		$address->appendText('countryCode', $this->countryCode, ShmNamespace::Common);

		return $address;
	}

	/**
	 * @throws InvalidLengthException
	 * @throws InvalidValueException
	 */
	public static function fromXml(XmlElement $xml): static
	{
		$address = new static();

		$streetName = $xml->child('streetName');

		if ($streetName !== null)
		{
			$address->streetName(trim($streetName->textContent));
		}

		$number = $xml->child('number');

		if ($number !== null)
		{
			$address->number(trim($number->textContent));
		}

		$box = $xml->text('box');

		if ($box !== null)
		{
			$address->box($box);
		}

		$postalCode = $xml->child('postalCode');

		if ($postalCode !== null)
		{
			$address->postalCode(trim($postalCode->textContent));
		}

		$locality = $xml->child('locality');

		if ($locality !== null)
		{
			$address->locality(trim($locality->textContent));
		}

		$countryCode = $xml->child('countryCode');

		if ($countryCode !== null)
		{
			$address->countryCode(trim($countryCode->textContent));
		}

		return $address;
	}
}
