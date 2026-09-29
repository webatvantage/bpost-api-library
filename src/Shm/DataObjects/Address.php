<?php

namespace Webatvantage\Bpost\Api\Shm\DataObjects;

use Dom\Element;
use Dom\XMLDocument;
use Webatvantage\Bpost\Api\Contracts\XmlDeserializable;
use Webatvantage\Bpost\Api\Contracts\XmlSerializable;
use Webatvantage\Bpost\Api\Shm\Support\Xml;
use Webatvantage\Bpost\Api\Support\Assert;

/**
 * A postal address.
 *
 * The element itself is named by the subclass — a pick-up point's address is <pugoAddress>, a
 * locker's is <parcelsDepotAddress> — but the children are always in the common namespace.
 */
class Address implements XmlDeserializable, XmlSerializable
{
	protected const string TAG_NAME = 'address';

	protected const ?string TAG_PREFIX = Xml::PREFIX_COMMON;

	public private(set) ?string $streetName = null;

	public private(set) ?string $number = null;

	public private(set) ?string $box = null;

	public private(set) ?string $postalCode = null;

	public private(set) ?string $locality = null;

	public private(set) string $countryCode = 'BE';

	public function streetName(string $streetName): static
	{
		$this->streetName = Assert::maxLength('streetName', $streetName, 40);

		return $this;
	}

	public function number(string|int $number): static
	{
		$this->number = Assert::maxLength('number', (string)$number, 8);

		return $this;
	}

	/**
	 * bpost documents a maximum of 8 here, but accepts 9 in practice and the library has relied on
	 * that since 3.7; see commits a7bdb27 and a97d061.
	 */
	public function box(string|int $box): static
	{
		$this->box = Assert::maxLength('box', (string)$box, 9);

		return $this;
	}

	public function postalCode(string|int $postalCode): static
	{
		$this->postalCode = Assert::maxLength('postalCode', (string)$postalCode, 40);

		return $this;
	}

	public function locality(string $locality): static
	{
		$this->locality = Assert::maxLength('locality', $locality, 40);

		return $this;
	}

	public function countryCode(string $countryCode): static
	{
		$this->countryCode = Assert::countryCode('countryCode', $countryCode);

		return $this;
	}

	public function toXml(XMLDocument $document, ?string $prefix = null): Element
	{
		$address = Xml::element($document, static::TAG_NAME, static::TAG_PREFIX);

		Xml::appendText($document, $address, 'streetName', $this->streetName, Xml::PREFIX_COMMON);
		Xml::appendText($document, $address, 'number', $this->number, Xml::PREFIX_COMMON);
		Xml::appendText($document, $address, 'box', $this->box, Xml::PREFIX_COMMON);
		Xml::appendText($document, $address, 'postalCode', $this->postalCode, Xml::PREFIX_COMMON);
		Xml::appendText($document, $address, 'locality', $this->locality, Xml::PREFIX_COMMON);
		Xml::appendText($document, $address, 'countryCode', $this->countryCode, Xml::PREFIX_COMMON);

		return $address;
	}

	public static function fromXml(Element $xml): static
	{
		$address = new static();

		$streetName = Xml::child($xml, 'streetName');

		if ($streetName !== null)
		{
			$address->streetName(trim($streetName->textContent));
		}

		$number = Xml::child($xml, 'number');

		if ($number !== null)
		{
			$address->number(trim($number->textContent));
		}

		$box = Xml::text($xml, 'box');

		if ($box !== null)
		{
			$address->box($box);
		}

		$postalCode = Xml::child($xml, 'postalCode');

		if ($postalCode !== null)
		{
			$address->postalCode(trim($postalCode->textContent));
		}

		$locality = Xml::child($xml, 'locality');

		if ($locality !== null)
		{
			$address->locality(trim($locality->textContent));
		}

		$countryCode = Xml::child($xml, 'countryCode');

		if ($countryCode !== null)
		{
			$address->countryCode(trim($countryCode->textContent));
		}

		return $address;
	}
}
