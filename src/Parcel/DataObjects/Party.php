<?php

namespace Webatvantage\Bpost\Api\Parcel\DataObjects;

use DOMDocument;
use DOMElement;
use SimpleXMLElement;
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

	public function toXml(DOMDocument $document, ?string $prefix = Xml::PREFIX_ANNOUNCEMENT): DOMElement
	{
		$party = $document->createElement(Xml::prefixed(static::TAG_NAME, $prefix));

		Xml::appendText($document, $party, 'name', $this->name, Xml::PREFIX_COMMON);
		Xml::appendText($document, $party, 'addressDepartment', $this->addressDepartment, Xml::PREFIX_COMMON);
		Xml::appendText($document, $party, 'addressContactName', $this->addressContactName, Xml::PREFIX_COMMON);
		Xml::appendText($document, $party, 'addressPlace', $this->addressPlace, Xml::PREFIX_COMMON);

		if ($this->address !== null)
		{
			$party->appendChild($this->address->toXml($document));
		}

		if ($this->contactDetail !== null)
		{
			$party->appendChild($this->contactDetail->toXml($document));
		}

		return $party;
	}

	public static function fromXml(SimpleXMLElement $xml): static
	{
		$party = new static();

		foreach (['name', 'addressDepartment', 'addressContactName', 'addressPlace'] as $field)
		{
			if (isset($xml->{$field}) && trim((string)$xml->{$field}) !== '')
			{
				$party->{$field}((string)$xml->{$field});
			}
		}

		if (isset($xml->address))
		{
			$party->address(Address::fromXml(Xml::readChildren($xml->address, Xml::COMMON)));
		}

		if (isset($xml->contactDetail))
		{
			$party->contactDetail(ContactDetail::fromXml(Xml::readChildren($xml->contactDetail, Xml::COMMON)));
		}

		return $party;
	}
}
