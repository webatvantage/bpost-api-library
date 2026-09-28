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
 * How to reach a party.
 *
 * On an international shipment bpost requires a phone or mobile number; on a national one all
 * three are optional.
 */
class ContactDetail implements XmlDeserializable, XmlSerializable
{
	public private(set) ?string $emailAddress = null;

	public private(set) ?string $telephoneNumber = null;

	public private(set) ?string $mobilePhone = null;

	public function emailAddress(string $emailAddress): static
	{
		$this->emailAddress = Assert::maxLength('emailAddress', $emailAddress, 40);

		return $this;
	}

	public function telephoneNumber(string $telephoneNumber): static
	{
		$this->telephoneNumber = Assert::maxLength('telephoneNumber', $telephoneNumber, 20);

		return $this;
	}

	public function mobilePhone(string $mobilePhone): static
	{
		$this->mobilePhone = Assert::maxLength('mobilePhone', $mobilePhone, 20);

		return $this;
	}

	public function hasPhoneNumber(): bool
	{
		return $this->telephoneNumber !== null || $this->mobilePhone !== null;
	}

	public function toXml(DOMDocument $document, ?string $prefix = Xml::PREFIX_COMMON): DOMElement
	{
		$detail = $document->createElement(Xml::prefixed('contactDetail', $prefix));

		Xml::appendText($document, $detail, 'emailAddress', $this->emailAddress, $prefix);
		Xml::appendText($document, $detail, 'telephoneNumber', $this->telephoneNumber, $prefix);
		Xml::appendText($document, $detail, 'mobilePhone', $this->mobilePhone, $prefix);

		return $detail;
	}

	public static function fromXml(SimpleXMLElement $xml): static
	{
		$detail = new static();

		foreach (['emailAddress', 'telephoneNumber', 'mobilePhone'] as $field)
		{
			if (isset($xml->{$field}) && trim((string)$xml->{$field}) !== '')
			{
				$detail->{$field}((string)$xml->{$field});
			}
		}

		return $detail;
	}
}
