<?php

namespace Webatvantage\Bpost\Api\Shm\DataObjects\Box;

use DOMDocument;
use DOMElement;
use SimpleXMLElement;
use Webatvantage\Bpost\Api\Contracts\XmlDeserializable;
use Webatvantage\Bpost\Api\Contracts\XmlSerializable;
use Webatvantage\Bpost\Api\Enums\Language;
use Webatvantage\Bpost\Api\Shm\Support\Xml;
use Webatvantage\Bpost\Api\Support\Assert;

/**
 * How to reach a receiver who is not a registered parcel locker user.
 *
 * bpost messages them the collection code, so at least one of mobilePhone and emailAddress has to
 * be usable.
 *
 * In v3.3 these were flat siblings inside at24-7 — messageLanguage, mobilePhone, email and a
 * reducedMobilityZone carrying "Y" or "N". v5 wraps them in <unregistered>, renames two of them,
 * and turns reducedMobilityZone into an empty flag. 3.x wrote the wrapper but named the flag
 * parcelLockerReducedMobilityZone, which bpost does not recognise.
 */
class Unregistered implements XmlDeserializable, XmlSerializable
{
	public private(set) ?Language $language = null;

	public private(set) ?string $mobilePhone = null;

	public private(set) ?string $emailAddress = null;

	public private(set) bool $reducedMobilityZone = false;

	/**
	 * The language the collection message is sent in. bpost accepts NL, FR and EN here.
	 */
	public function language(Language $language): static
	{
		$this->language = $language;

		return $this;
	}

	public function mobilePhone(string $mobilePhone): static
	{
		$this->mobilePhone = Assert::maxLength('mobilePhone', $mobilePhone, 20);

		return $this;
	}

	public function emailAddress(string $emailAddress): static
	{
		$this->emailAddress = Assert::maxLength('emailAddress', $emailAddress, 50);

		return $this;
	}

	/**
	 * Ask for a locker the receiver can reach without stretching or bending.
	 */
	public function reducedMobilityZone(bool $reducedMobilityZone = true): static
	{
		$this->reducedMobilityZone = $reducedMobilityZone;

		return $this;
	}

	public function toXml(DOMDocument $document, ?string $prefix = null): DOMElement
	{
		$element = $document->createElement(Xml::prefixed('unregistered', $prefix));

		Xml::appendText($document, $element, 'language', $this->language?->value, $prefix);
		Xml::appendText($document, $element, 'mobilePhone', $this->mobilePhone, $prefix);
		Xml::appendText($document, $element, 'emailAddress', $this->emailAddress, $prefix);

		if ($this->reducedMobilityZone)
		{
			$element->appendChild($document->createElement(Xml::prefixed('reducedMobilityZone', $prefix)));
		}

		return $element;
	}

	public static function fromXml(SimpleXMLElement $xml): static
	{
		$unregistered = new static();

		if (isset($xml->language) && trim((string)$xml->language) !== '')
		{
			$unregistered->language(Language::from(strtoupper(trim((string)$xml->language))));
		}

		if (isset($xml->mobilePhone) && trim((string)$xml->mobilePhone) !== '')
		{
			$unregistered->mobilePhone((string)$xml->mobilePhone);
		}

		if (isset($xml->emailAddress) && trim((string)$xml->emailAddress) !== '')
		{
			$unregistered->emailAddress((string)$xml->emailAddress);
		}

		// Present at all means yes. v3.3 spelled it as a Y/N value, so a literal "N" is honoured
		// too for anyone replaying an older response.
		if (isset($xml->reducedMobilityZone))
		{
			$unregistered->reducedMobilityZone(strtoupper(trim((string)$xml->reducedMobilityZone)) !== 'N');
		}

		return $unregistered;
	}
}
