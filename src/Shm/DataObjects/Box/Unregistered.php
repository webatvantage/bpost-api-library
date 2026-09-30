<?php

namespace Webatvantage\Bpost\Api\Shm\DataObjects\Box;

use Webatvantage\Bpost\Api\Contracts\XmlDeserializable;
use Webatvantage\Bpost\Api\Contracts\XmlNamespace;
use Webatvantage\Bpost\Api\Contracts\XmlSerializable;
use Webatvantage\Bpost\Api\Enums\Language;
use Webatvantage\Bpost\Api\Exceptions\InvalidArgumentException;
use Webatvantage\Bpost\Api\Exceptions\InvalidLengthException;
use Webatvantage\Bpost\Api\Exceptions\UnexpectedValueException;
use Webatvantage\Bpost\Api\Support\Validate;
use Webatvantage\Bpost\Api\Support\XmlElement;

/**
 * How to reach a receiver who is not a registered parcel locker user.
 *
 * bpost messages them the collection code, so at least one of mobilePhone and emailAddress has to
 * be usable.
 *
 * In v3.3 these were flat siblings inside at24-7 — messageLanguage, mobilePhone, email and a
 * reducedMobilityZone carrying "Y" or "N". v5 wraps them in <unregistered>, renames two of them,
 * and turns reducedMobilityZone into an empty flag.
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

	/**
	 * @throws InvalidLengthException
	 */
	public function mobilePhone(string $mobilePhone): static
	{
		$this->mobilePhone = Validate::maxLength('mobilePhone', $mobilePhone, 20);

		return $this;
	}

	/**
	 * @throws InvalidLengthException
	 */
	public function emailAddress(string $emailAddress): static
	{
		$this->emailAddress = Validate::maxLength('emailAddress', $emailAddress, 50);

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

	/**
	 * @throws InvalidArgumentException
	 */
	public function toXml(XmlElement $parent, ?XmlNamespace $namespace = null): XmlElement
	{
		$element = $parent->appendElement('unregistered', $namespace);

		$element->appendText('language', $this->language?->value, $namespace);
		$element->appendText('mobilePhone', $this->mobilePhone, $namespace);
		$element->appendText('emailAddress', $this->emailAddress, $namespace);

		if ($this->reducedMobilityZone)
		{
			$element->appendElement('reducedMobilityZone', $namespace);
		}

		return $element;
	}

	/**
	 * @throws UnexpectedValueException
	 */
	public static function fromXml(XmlElement $xml): static
	{
		$unregistered = new static();
		$language = $xml->text('language');

		$unregistered->language = isset($language)
			? Validate::enum('language', Language::class, strtoupper($language))
			: null;
		$unregistered->mobilePhone = $xml->text('mobilePhone');
		$unregistered->emailAddress = $xml->text('emailAddress');

		// Present at all means yes. v3.3 spelled it as a Y/N value, so a literal "N" is honoured
		// too for anyone replaying an older response.
		$reducedMobilityZone = $xml->child('reducedMobilityZone');

		$unregistered->reducedMobilityZone = isset($reducedMobilityZone)
			&& strtoupper(trim($reducedMobilityZone->textContent)) !== 'N';

		return $unregistered;
	}
}
