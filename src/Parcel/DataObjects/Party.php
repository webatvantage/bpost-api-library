<?php

namespace Webatvantage\Bpost\Api\Parcel\DataObjects;

use Webatvantage\Bpost\Api\Contracts\XmlDeserializable;
use Webatvantage\Bpost\Api\Contracts\XmlNamespace;
use Webatvantage\Bpost\Api\Contracts\XmlSerializable;
use Webatvantage\Bpost\Api\Parcel\Enums\ParcelNamespace;
use Webatvantage\Bpost\Api\Support\Assert;
use Webatvantage\Bpost\Api\Support\XmlElement;

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

	public function toXml(XmlElement $parent, ?XmlNamespace $namespace = ParcelNamespace::Announcement): XmlElement
	{
		$party = $parent->appendElement(static::TAG_NAME, $namespace);

		$party->appendText('name', $this->name, ParcelNamespace::Common);
		$party->appendText('addressDepartment', $this->addressDepartment, ParcelNamespace::Common);
		$party->appendText('addressContactName', $this->addressContactName, ParcelNamespace::Common);
		$party->appendText('addressPlace', $this->addressPlace, ParcelNamespace::Common);

		if ($this->address !== null)
		{
			$this->address->toXml($party);
		}

		if ($this->contactDetail !== null)
		{
			$this->contactDetail->toXml($party);
		}

		return $party;
	}

	public static function fromXml(XmlElement $xml): static
	{
		$party = new static();

		foreach (['name', 'addressDepartment', 'addressContactName', 'addressPlace'] as $field)
		{
			$value = $xml->text($field);

			if ($value !== null)
			{
				$party->{$field}($value);
			}
		}

		$address = $xml->child('address');

		if ($address !== null)
		{
			$party->address(Address::fromXml($address));
		}

		$contactDetail = $xml->child('contactDetail');

		if ($contactDetail !== null)
		{
			$party->contactDetail(ContactDetail::fromXml($contactDetail));
		}

		return $party;
	}
}
