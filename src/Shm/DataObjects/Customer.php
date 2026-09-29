<?php

namespace Webatvantage\Bpost\Api\Shm\DataObjects;

use Webatvantage\Bpost\Api\Contracts\XmlDeserializable;
use Webatvantage\Bpost\Api\Contracts\XmlNamespace;
use Webatvantage\Bpost\Api\Contracts\XmlSerializable;
use Webatvantage\Bpost\Api\Shm\Enums\ShmNamespace;
use Webatvantage\Bpost\Api\Support\Validate;
use Webatvantage\Bpost\Api\Support\XmlElement;

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
		$this->name = Validate::maxLength('name', $name, 40);

		return $this;
	}

	public function company(string $company): static
	{
		$this->company = Validate::maxLength('company', $company, 40);

		return $this;
	}

	public function address(Address $address): static
	{
		$this->address = $address;

		return $this;
	}

	public function emailAddress(string $emailAddress): static
	{
		$this->emailAddress = Validate::maxLength('emailAddress', $emailAddress, 50);

		return $this;
	}

	public function phoneNumber(string $phoneNumber): static
	{
		$this->phoneNumber = Validate::maxLength('phoneNumber', $phoneNumber, 20);

		return $this;
	}

	public function toXml(XmlElement $parent, ?XmlNamespace $namespace = null): XmlElement
	{
		$customer = $parent->appendElement(static::TAG_NAME, $namespace);

		$customer->appendText('name', $this->name, ShmNamespace::Common);
		$customer->appendText('company', $this->company, ShmNamespace::Common);

		if ($this->address !== null)
		{
			$this->address->toXml($customer);
		}

		$customer->appendText('emailAddress', $this->emailAddress, ShmNamespace::Common);
		$customer->appendText('phoneNumber', $this->phoneNumber, ShmNamespace::Common);

		return $customer;
	}

	/**
	 * @param XmlElement $xml The customer element's children, already in the common namespace
	 */
	public static function fromXml(XmlElement $xml): static
	{
		$customer = new static();

		$name = $xml->child('name');

		if ($name !== null)
		{
			$customer->name(trim($name->textContent));
		}

		$company = $xml->child('company');

		if ($company !== null)
		{
			$customer->company(trim($company->textContent));
		}

		$address = $xml->child('address');

		if ($address !== null)
		{
			$customer->address(Address::fromXml($address));
		}

		$emailAddress = $xml->child('emailAddress');

		if ($emailAddress !== null)
		{
			$customer->emailAddress(trim($emailAddress->textContent));
		}

		$phoneNumber = $xml->child('phoneNumber');

		if ($phoneNumber !== null)
		{
			$customer->phoneNumber(trim($phoneNumber->textContent));
		}

		return $customer;
	}
}
