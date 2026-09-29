<?php

namespace Webatvantage\Bpost\Api\Parcel\DataObjects;

use Dom\Element;
use Dom\XMLDocument;
use Webatvantage\Bpost\Api\Contracts\XmlDeserializable;
use Webatvantage\Bpost\Api\Contracts\XmlSerializable;
use Webatvantage\Bpost\Api\Parcel\Support\Xml;
use Webatvantage\Bpost\Api\Support\Assert;

/**
 * A postal address as the announcement and tracking services spell it.
 *
 * Deliberately not the Shipping Manager's Address: this service calls the same fields
 * houseNumber, boxNumber and city where the Shipping Manager calls them number, box and locality.
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
	 */
	public function streetName(string $streetName): static
	{
		$this->streetName = Assert::maxLength('streetName', $streetName, 40);

		return $this;
	}

	public function houseNumber(string|int $houseNumber): static
	{
		$this->houseNumber = Assert::maxLength('houseNumber', (string)$houseNumber, 8);

		return $this;
	}

	public function boxNumber(string|int $boxNumber): static
	{
		$this->boxNumber = Assert::maxLength('boxNumber', (string)$boxNumber, 8);

		return $this;
	}

	public function postalCode(string|int $postalCode): static
	{
		$this->postalCode = (string)$postalCode;

		return $this;
	}

	public function city(string $city): static
	{
		$this->city = Assert::maxLength('city', $city, 40);

		return $this;
	}

	public function countryCode(string $countryCode): static
	{
		$this->countryCode = Assert::countryCode('countryCode', $countryCode);

		return $this;
	}

	public function toXml(XMLDocument $document, ?string $prefix = Xml::PREFIX_COMMON): Element
	{
		$address = Xml::element($document, 'address', $prefix);

		Xml::appendText($document, $address, 'streetName', $this->streetName, $prefix);
		Xml::appendText($document, $address, 'houseNumber', $this->houseNumber, $prefix);
		Xml::appendText($document, $address, 'boxNumber', $this->boxNumber, $prefix);
		Xml::appendText($document, $address, 'postalCode', $this->postalCode, $prefix);
		Xml::appendText($document, $address, 'city', $this->city, $prefix);
		Xml::appendText($document, $address, 'countryCode', $this->countryCode, $prefix);

		return $address;
	}

	public static function fromXml(Element $xml): static
	{
		$address = new static();

		foreach (['streetName', 'houseNumber', 'boxNumber', 'postalCode', 'city', 'countryCode'] as $field)
		{
			$value = Xml::text($xml, $field);

			if ($value !== null)
			{
				$address->{$field}($value);
			}
		}

		return $address;
	}
}
