<?php

namespace Webatvantage\Bpost\Api\Parcel\DataObjects;

use Dom\Element;
use Dom\XMLDocument;
use Webatvantage\Bpost\Api\Contracts\XmlDeserializable;
use Webatvantage\Bpost\Api\Contracts\XmlSerializable;
use Webatvantage\Bpost\Api\Parcel\Support\Xml;
use Webatvantage\Bpost\Api\Support\Assert;

/**
 * A party on an announced parcel.
 *
 * Richer than the Shipping Manager's equivalent: this service takes a department, a contact person
 * and a free-text place alongside the name.
 */
abstract class Party implements XmlDeserializable, XmlSerializable
{
	protected const string TAG_NAME = 'party';

	public private(set) ?string $name = null;

	public private(set) ?string $addressDepartment = null;

	public private(set) ?string $addressContactName = null;

	public private(set) ?string $addressPlace = null;

	public private(set) ?Address $address = null;

	public private(set) ?ContactDetail $contactDetail = null;

	/**
	 * The company name, or a private person's name.
	 */
	public function name(string $name): static
	{
		$this->name = Assert::maxLength('name', $name, 40);

		return $this;
	}

	public function addressDepartment(string $addressDepartment): static
	{
		$this->addressDepartment = Assert::maxLength('addressDepartment', $addressDepartment, 40);

		return $this;
	}

	public function addressContactName(string $addressContactName): static
	{
		$this->addressContactName = Assert::maxLength('addressContactName', $addressContactName, 40);

		return $this;
	}

	/**
	 * Extra information, such as a building or floor.
	 */
	public function addressPlace(string $addressPlace): static
	{
		$this->addressPlace = Assert::maxLength('addressPlace', $addressPlace, 40);

		return $this;
	}

	public function address(Address $address): static
	{
		$this->address = $address;

		return $this;
	}

	public function contactDetail(ContactDetail $contactDetail): static
	{
		$this->contactDetail = $contactDetail;

		return $this;
	}

	public function toXml(XMLDocument $document, ?string $prefix = Xml::PREFIX_ANNOUNCEMENT): Element
	{
		$party = Xml::element($document, static::TAG_NAME, $prefix);

		Xml::appendText($document, $party, 'name', $this->name, Xml::PREFIX_COMMON);
		Xml::appendText($document, $party, 'addressDepartment', $this->addressDepartment, Xml::PREFIX_COMMON);
		Xml::appendText($document, $party, 'addressContactName', $this->addressContactName, Xml::PREFIX_COMMON);
		Xml::appendText($document, $party, 'addressPlace', $this->addressPlace, Xml::PREFIX_COMMON);

		if ($this->address !== null)
		{
			$party->append($this->address->toXml($document));
		}

		if ($this->contactDetail !== null)
		{
			$party->append($this->contactDetail->toXml($document));
		}

		return $party;
	}

	public static function fromXml(Element $xml): static
	{
		$party = new static();

		foreach (['name', 'addressDepartment', 'addressContactName', 'addressPlace'] as $field)
		{
			$value = Xml::text($xml, $field);

			if ($value !== null)
			{
				$party->{$field}($value);
			}
		}

		$address = Xml::child($xml, 'address');

		if ($address !== null)
		{
			$party->address(Address::fromXml($address));
		}

		$contactDetail = Xml::child($xml, 'contactDetail');

		if ($contactDetail !== null)
		{
			$party->contactDetail(ContactDetail::fromXml($contactDetail));
		}

		return $party;
	}
}
