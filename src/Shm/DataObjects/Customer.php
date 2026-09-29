<?php

namespace Webatvantage\Bpost\Api\Shm\DataObjects;

use Dom\Element;
use Dom\XMLDocument;
use Webatvantage\Bpost\Api\Contracts\XmlDeserializable;
use Webatvantage\Bpost\Api\Contracts\XmlSerializable;
use Webatvantage\Bpost\Api\Shm\Support\Xml;
use Webatvantage\Bpost\Api\Support\Assert;

/**
 * A party on a shipment. Sender and Receiver differ only in the element they are written under.
 */
abstract class Customer implements XmlDeserializable, XmlSerializable
{
	protected const string TAG_NAME = 'customer';

	public private(set) ?string $name = null;

	public private(set) ?string $company = null;

	public private(set) ?Address $address = null;

	public private(set) ?string $emailAddress = null;

	public private(set) ?string $phoneNumber = null;

	/**
	 * Only the first 40 characters are printed on the label, so bpost rejects anything longer.
	 */
	public function name(string $name): static
	{
		$this->name = Assert::maxLength('name', $name, 40);

		return $this;
	}

	public function company(string $company): static
	{
		$this->company = Assert::maxLength('company', $company, 40);

		return $this;
	}

	public function address(Address $address): static
	{
		$this->address = $address;

		return $this;
	}

	public function emailAddress(string $emailAddress): static
	{
		$this->emailAddress = Assert::maxLength('emailAddress', $emailAddress, 50);

		return $this;
	}

	public function phoneNumber(string $phoneNumber): static
	{
		$this->phoneNumber = Assert::maxLength('phoneNumber', $phoneNumber, 20);

		return $this;
	}

	public function toXml(XMLDocument $document, ?string $prefix = null): Element
	{
		$customer = Xml::element($document, static::TAG_NAME, $prefix);

		Xml::appendText($document, $customer, 'name', $this->name, Xml::PREFIX_COMMON);
		Xml::appendText($document, $customer, 'company', $this->company, Xml::PREFIX_COMMON);

		if ($this->address !== null)
		{
			$customer->append($this->address->toXml($document));
		}

		Xml::appendText($document, $customer, 'emailAddress', $this->emailAddress, Xml::PREFIX_COMMON);
		Xml::appendText($document, $customer, 'phoneNumber', $this->phoneNumber, Xml::PREFIX_COMMON);

		return $customer;
	}

	/**
	 * @param Element $xml The customer element's children, already in the common namespace
	 */
	public static function fromXml(Element $xml): static
	{
		$customer = new static();

		$name = Xml::child($xml, 'name');

		if ($name !== null)
		{
			$customer->name(trim($name->textContent));
		}

		$company = Xml::child($xml, 'company');

		if ($company !== null)
		{
			$customer->company(trim($company->textContent));
		}

		$address = Xml::child($xml, 'address');

		if ($address !== null)
		{
			$customer->address(Address::fromXml($address));
		}

		$emailAddress = Xml::child($xml, 'emailAddress');

		if ($emailAddress !== null)
		{
			$customer->emailAddress(trim($emailAddress->textContent));
		}

		$phoneNumber = Xml::child($xml, 'phoneNumber');

		if ($phoneNumber !== null)
		{
			$customer->phoneNumber(trim($phoneNumber->textContent));
		}

		return $customer;
	}
}
