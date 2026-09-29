<?php

namespace Webatvantage\Bpost\Api\Parcel\DataObjects;

use Webatvantage\Bpost\Api\Contracts\XmlDeserializable;
use Webatvantage\Bpost\Api\Contracts\XmlNamespace;
use Webatvantage\Bpost\Api\Contracts\XmlSerializable;
use Webatvantage\Bpost\Api\Exceptions\InvalidLengthException;
use Webatvantage\Bpost\Api\Exceptions\InvalidValueException;
use Webatvantage\Bpost\Api\Parcel\Enums\ParcelNamespace;
use Webatvantage\Bpost\Api\Support\Validate;
use Webatvantage\Bpost\Api\Support\XmlElement;

/**
 * A postal address as the announcement and tracking services spell it
 */
class Address implements XmlDeserializable, XmlSerializable
{
	public private(set) ?string $streetName = null;

	public private(set) ?string $houseNumber = null;

	public private(set) ?string $boxNumber = null;

	public private(set) ?string $postalCode = null;

	public private(set) ?string $city = null;

	public private(set) string $countryCode = 'BE';

	/**
	 * bpost prefers the house number given separately rather than folded into the street name.
	 *
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
	public function houseNumber(string|int $houseNumber): static
	{
		$this->houseNumber = Validate::maxLength('houseNumber', (string)$houseNumber, 8);

		return $this;
	}

	/**
	 * @throws InvalidLengthException
	 */
	public function boxNumber(string|int $boxNumber): static
	{
		$this->boxNumber = Validate::maxLength('boxNumber', (string)$boxNumber, 8);

		return $this;
	}

	public function postalCode(string|int $postalCode): static
	{
		$this->postalCode = (string)$postalCode;

		return $this;
	}

	/**
	 * @throws InvalidLengthException
	 */
	public function city(string $city): static
	{
		$this->city = Validate::maxLength('city', $city, 40);

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

	public function toXml(XmlElement $parent, ?XmlNamespace $namespace = ParcelNamespace::Common): XmlElement
	{
		$address = $parent->appendElement('address', $namespace);

		$address->appendText('streetName', $this->streetName, $namespace);
		$address->appendText('houseNumber', $this->houseNumber, $namespace);
		$address->appendText('boxNumber', $this->boxNumber, $namespace);
		$address->appendText('postalCode', $this->postalCode, $namespace);
		$address->appendText('city', $this->city, $namespace);
		$address->appendText('countryCode', $this->countryCode, $namespace);

		return $address;
	}

	/**
	 * @throws InvalidLengthException
	 * @throws InvalidValueException
	 */
	public static function fromXml(XmlElement $xml): static
	{
		$address = new static();

		foreach (['streetName', 'houseNumber', 'boxNumber', 'postalCode', 'city', 'countryCode'] as $field)
		{
			$value = $xml->text($field);

			if ($value !== null)
			{
				$address->{$field}($value);
			}
		}

		return $address;
	}
}
