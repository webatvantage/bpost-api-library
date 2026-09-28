<?php

namespace Webatvantage\Bpost\Api\Shm\DataObjects;

use DOMDocument;
use DOMElement;
use SimpleXMLElement;
use Webatvantage\Bpost\Api\Contracts\XmlDeserializable;
use Webatvantage\Bpost\Api\Contracts\XmlSerializable;
use Webatvantage\Bpost\Api\Shm\Support\Assert;
use Webatvantage\Bpost\Api\Shm\Support\Xml;

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

	public function toXml(DOMDocument $document, ?string $prefix = null): DOMElement
	{
		$customer = $document->createElement(Xml::prefixed(static::TAG_NAME, $prefix));

		Xml::appendText($document, $customer, 'name', $this->name, Xml::PREFIX_COMMON);
		Xml::appendText($document, $customer, 'company', $this->company, Xml::PREFIX_COMMON);

		if ($this->address !== null)
		{
			$customer->appendChild($this->address->toXml($document));
		}

		Xml::appendText($document, $customer, 'emailAddress', $this->emailAddress, Xml::PREFIX_COMMON);
		Xml::appendText($document, $customer, 'phoneNumber', $this->phoneNumber, Xml::PREFIX_COMMON);

		return $customer;
	}

	/**
	 * @param SimpleXMLElement $xml The customer element's children, already in the common namespace
	 */
	public static function fromXml(SimpleXMLElement $xml): static
	{
		$customer = new static();

		if (isset($xml->name))
		{
			$customer->name((string)$xml->name);
		}

		if (isset($xml->company))
		{
			$customer->company((string)$xml->company);
		}

		if (isset($xml->address))
		{
			$customer->address(Address::fromXml($xml->address));
		}

		if (isset($xml->emailAddress))
		{
			$customer->emailAddress((string)$xml->emailAddress);
		}

		if (isset($xml->phoneNumber))
		{
			$customer->phoneNumber((string)$xml->phoneNumber);
		}

		return $customer;
	}
}
