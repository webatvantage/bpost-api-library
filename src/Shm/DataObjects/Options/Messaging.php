<?php

namespace Webatvantage\Bpost\Api\Shm\DataObjects\Options;

use Webatvantage\Bpost\Api\Contracts\Option;
use Webatvantage\Bpost\Api\Contracts\XmlDeserializable;
use Webatvantage\Bpost\Api\Contracts\XmlNamespace;
use Webatvantage\Bpost\Api\Enums\Language;
use Webatvantage\Bpost\Api\Exceptions\InvalidLengthException;
use Webatvantage\Bpost\Api\Exceptions\InvalidValueException;
use Webatvantage\Bpost\Api\Exceptions\UnexpectedValueException;
use Webatvantage\Bpost\Api\Shm\Enums\MessagingType;
use Webatvantage\Bpost\Api\Shm\Enums\ShmNamespace;
use Webatvantage\Bpost\Api\Support\Validate;
use Webatvantage\Bpost\Api\Support\XmlElement;

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
	 * @throws InvalidLengthException
	 * @throws InvalidValueException
	 */
	public function email(string $emailAddress): static
	{
		$this->assertNoChannelYet('emailAddress');
		$this->emailAddress = Validate::maxLength('emailAddress', $emailAddress, 50);

		return $this;
	}

	/**
	 * @throws InvalidLengthException
	 * @throws InvalidValueException
	 */
	public function sms(string $mobilePhone): static
	{
		$this->assertNoChannelYet('mobilePhone');
		$this->mobilePhone = Validate::maxLength('mobilePhone', $mobilePhone, 20);

		return $this;
	}

	public function toXml(XmlElement $parent, ?XmlNamespace $namespace = ShmNamespace::Common): XmlElement
	{
		$element = $parent->appendElement($this->type->value, $namespace);
		$element->setAttribute('language', $this->language->value);

		$element->appendText('emailAddress', $this->emailAddress, $namespace);
		$element->appendText('mobilePhone', $this->mobilePhone, $namespace);

		return $element;
	}

	/**
	 * @throws UnexpectedValueException
	 */
	public static function fromXml(XmlElement $xml): static
	{
		$type = Validate::enum('type', MessagingType::class, $xml->localName);
		$language = Language::tryFrom(strtoupper((string)$xml->attribute('language'))) ?? Language::EN;

		$messaging = new static($type, $language);
		$emailAddress = $xml->text('emailAddress');

		if ($emailAddress !== null)
		{
			$messaging->emailAddress = $emailAddress;
		}

		$mobilePhone = $xml->text('mobilePhone');

		if ($mobilePhone !== null)
		{
			$messaging->mobilePhone = $mobilePhone;
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
