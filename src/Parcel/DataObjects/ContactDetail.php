<?php

namespace Webatvantage\Bpost\Api\Parcel\DataObjects;

use Webatvantage\Bpost\Api\Contracts\XmlDeserializable;
use Webatvantage\Bpost\Api\Contracts\XmlNamespace;
use Webatvantage\Bpost\Api\Contracts\XmlSerializable;
use Webatvantage\Bpost\Api\Parcel\Enums\ParcelNamespace;
use Webatvantage\Bpost\Api\Support\Validate;
use Webatvantage\Bpost\Api\Support\XmlElement;

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
		$this->emailAddress = Validate::maxLength('emailAddress', $emailAddress, 40);

		return $this;
	}

	public function telephoneNumber(string $telephoneNumber): static
	{
		$this->telephoneNumber = Validate::maxLength('telephoneNumber', $telephoneNumber, 20);

		return $this;
	}

	public function mobilePhone(string $mobilePhone): static
	{
		$this->mobilePhone = Validate::maxLength('mobilePhone', $mobilePhone, 20);

		return $this;
	}

	public function hasPhoneNumber(): bool
	{
		return $this->telephoneNumber !== null || $this->mobilePhone !== null;
	}

	public function toXml(XmlElement $parent, ?XmlNamespace $namespace = ParcelNamespace::Common): XmlElement
	{
		$detail = $parent->appendElement('contactDetail', $namespace);

		$detail->appendText('emailAddress', $this->emailAddress, $namespace);
		$detail->appendText('telephoneNumber', $this->telephoneNumber, $namespace);
		$detail->appendText('mobilePhone', $this->mobilePhone, $namespace);

		return $detail;
	}

	public static function fromXml(XmlElement $xml): static
	{
		$detail = new static();

		foreach (['emailAddress', 'telephoneNumber', 'mobilePhone'] as $field)
		{
			$value = $xml->text($field);

			if ($value !== null)
			{
				$detail->{$field}($value);
			}
		}

		return $detail;
	}
}
