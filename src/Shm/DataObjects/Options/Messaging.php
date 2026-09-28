<?php

namespace Webatvantage\Bpost\Api\Shm\DataObjects\Options;

use DOMDocument;
use DOMElement;
use SimpleXMLElement;
use Webatvantage\Bpost\Api\Contracts\Option;
use Webatvantage\Bpost\Api\Contracts\XmlDeserializable;
use Webatvantage\Bpost\Api\Enums\Language;
use Webatvantage\Bpost\Api\Exceptions\InvalidValueException;
use Webatvantage\Bpost\Api\Shm\Enums\MessagingType;
use Webatvantage\Bpost\Api\Shm\Support\Xml;
use Webatvantage\Bpost\Api\Support\Assert;

/**
 * A notification to the sender or the receiver.
 *
 * Each message goes to exactly one address: bpost allows email or SMS per notification, never
 * both, though different notifications on the same box may use different channels.
 */
class Messaging implements Option, XmlDeserializable
{
	public private(set) ?string $emailAddress = null;

	public private(set) ?string $mobilePhone = null;

	public function __construct(public private(set) MessagingType $type, public private(set) Language $language = Language::EN) {}

	/** The parcel has been delivered. */
	public static function infoDistributed(Language $language = Language::EN): static
	{
		return new static(MessagingType::InfoDistributed, $language);
	}

	/** The parcel arrives the next business day. */
	public static function infoNextDay(Language $language = Language::EN): static
	{
		return new static(MessagingType::InfoNextDay, $language);
	}

	/** The parcel is waiting at the post office. */
	public static function infoReminder(Language $language = Language::EN): static
	{
		return new static(MessagingType::InfoReminder, $language);
	}

	/** The parcel is available at the pick-up point. bpack@bpost only. */
	public static function keepMeInformed(Language $language = Language::EN): static
	{
		return new static(MessagingType::KeepMeInformed, $language);
	}

	/**
	 * @throws InvalidValueException
	 */
	public function email(string $emailAddress): static
	{
		$this->assertNoChannelYet('emailAddress');
		$this->emailAddress = Assert::maxLength('emailAddress', $emailAddress, 50);

		return $this;
	}

	/**
	 * @throws InvalidValueException
	 */
	public function sms(string $mobilePhone): static
	{
		$this->assertNoChannelYet('mobilePhone');
		$this->mobilePhone = Assert::maxLength('mobilePhone', $mobilePhone, 20);

		return $this;
	}

	public function toXml(DOMDocument $document, ?string $prefix = Xml::PREFIX_COMMON): DOMElement
	{
		$element = $document->createElement(Xml::prefixed($this->type->value, $prefix));
		$element->setAttribute('language', $this->language->value);

		Xml::appendText($document, $element, 'emailAddress', $this->emailAddress, $prefix);
		Xml::appendText($document, $element, 'mobilePhone', $this->mobilePhone, $prefix);

		return $element;
	}

	public static function fromXml(SimpleXMLElement $xml): static
	{
		$type = Assert::enum('type', MessagingType::class, $xml->getName());
		$language = Language::tryFrom(strtoupper((string)Xml::attribute($xml, 'language'))) ?? Language::EN;

		$messaging = new static($type, $language);
		$children = Xml::readChildren($xml, Xml::READ_COMMON);

		if (isset($children->emailAddress) && trim((string)$children->emailAddress) !== '')
		{
			$messaging->emailAddress = trim((string)$children->emailAddress);
		}

		if (isset($children->mobilePhone) && trim((string)$children->mobilePhone) !== '')
		{
			$messaging->mobilePhone = trim((string)$children->mobilePhone);
		}

		return $messaging;
	}

	/**
	 * @throws InvalidValueException
	 */
	private function assertNoChannelYet(string $setting): void
	{
		if ($this->emailAddress === null && $this->mobilePhone === null)
		{
			return;
		}

		throw new InvalidValueException(
			name: $setting,
			value: $this->emailAddress ?? $this->mobilePhone,
			allowed: ['one of emailAddress or mobilePhone, not both'],
		);
	}
}
