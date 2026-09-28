<?php

namespace Webatvantage\Bpost\Api\Parcel\DataObjects\Options;

use DOMDocument;
use DOMElement;
use Webatvantage\Bpost\Api\Contracts\Option;
use Webatvantage\Bpost\Api\Enums\Language;
use Webatvantage\Bpost\Api\Exceptions\InvalidValueException;
use Webatvantage\Bpost\Api\Parcel\Support\Xml;
use Webatvantage\Bpost\Api\Support\Assert;

/**
 * A notification to the receiver.
 *
 * The language is an element here rather than an attribute, and the SMS field is smsNumber rather
 * than mobilePhone — both differ from the Shipping Manager's spelling of the same idea.
 */
class Notification implements Option
{
	public private(set) ?string $emailAddress = null;

	public private(set) ?string $smsNumber = null;

	private function __construct(private readonly string $tagName, public private(set) Language $language = Language::EN) {}

	/** The parcel has been delivered. */
	public static function infoDistributed(Language $language = Language::EN): static
	{
		return new static('infoDistributed', $language);
	}

	/** The parcel arrives the next business day. */
	public static function infoNextDay(Language $language = Language::EN): static
	{
		return new static('infoNextDay', $language);
	}

	/** The parcel is waiting to be collected. */
	public static function infoReminder(Language $language = Language::EN): static
	{
		return new static('infoReminder', $language);
	}

	/**
	 * @throws InvalidValueException
	 */
	public function email(string $emailAddress): static
	{
		$this->assertNoChannelYet();
		$this->emailAddress = Assert::maxLength('emailAddress', $emailAddress, 50);

		return $this;
	}

	/**
	 * @throws InvalidValueException
	 */
	public function sms(string $smsNumber): static
	{
		$this->assertNoChannelYet();
		$this->smsNumber = Assert::maxLength('smsNumber', $smsNumber, 20);

		return $this;
	}

	public function toXml(DOMDocument $document, ?string $prefix = Xml::PREFIX_COMMON): DOMElement
	{
		$element = $document->createElement(Xml::prefixed($this->tagName, $prefix));

		Xml::appendText($document, $element, 'language', $this->language->value, $prefix);
		Xml::appendText($document, $element, 'emailAddress', $this->emailAddress, $prefix);
		Xml::appendText($document, $element, 'smsNumber', $this->smsNumber, $prefix);

		return $element;
	}

	/**
	 * @throws InvalidValueException
	 */
	private function assertNoChannelYet(): void
	{
		if ($this->emailAddress === null && $this->smsNumber === null)
		{
			return;
		}

		throw new InvalidValueException(
			name: $this->tagName,
			value: $this->emailAddress ?? $this->smsNumber,
			allowed: ['one of emailAddress or smsNumber, not both'],
		);
	}
}
