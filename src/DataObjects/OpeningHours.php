<?php

namespace Webatvantage\Bpost\Api\DataObjects;

use Webatvantage\Bpost\Api\Contracts\XmlDeserializable;
use Webatvantage\Bpost\Api\Contracts\XmlNamespace;
use Webatvantage\Bpost\Api\Contracts\XmlSerializable;
use Webatvantage\Bpost\Api\Enums\Weekday;
use Webatvantage\Bpost\Api\Exceptions\InvalidValueException;
use Webatvantage\Bpost\Api\Support\XmlElement;

/**
 * When the receiving business is open.
 *
 * Each day is one string rather than a structure, because that is how bpost spells it:
 * "09:00-17:00" for one range, "09:00-12:00/13:00-17:30" for two, "-/-" or "-" for closed, and an
 * empty value for unknown. Unlike the Geolocator's opening hours, which are four separate
 * elements per day.
 *
 * Shared because the Shipping Manager and the announcement service use the identical format; they
 * disagree only on the element name, which is why toXml takes one.
 */
class OpeningHours implements XmlDeserializable, XmlSerializable
{
	private const string RANGE = '(?:[01]\d|2[0-3]):[0-5]\d-(?:[01]\d|2[0-3]):[0-5]\d';

	/** @var array<string, string> */
	private array $days = [];

	/**
	 * @param string $hours One or two ranges, or "-" / "-/-" for closed
	 *
	 * @throws InvalidValueException
	 */
	public function on(Weekday $weekday, string $hours): static
	{
		$this->days[$weekday->value] = self::assertRange($weekday, $hours);

		return $this;
	}

	public function closed(Weekday $weekday): static
	{
		$this->days[$weekday->value] = '-/-';

		return $this;
	}

	public function for(Weekday $weekday): ?string
	{
		return $this->days[$weekday->value] ?? null;
	}

	/**
	 * @return array<string, string>
	 */
	public function all(): array
	{
		return $this->days;
	}

	public function isEmpty(): bool
	{
		return count($this->days) === 0;
	}

	/**
	 * The Shipping Manager names this block openingHours, an announcement names it
	 * receiverOpeningHours, so the caller supplies the tag along with its namespace.
	 */
	public function toXml(
		XmlElement $parent,
		?XmlNamespace $namespace = null,
		string $tagName = 'openingHours',
	): XmlElement {
		$element = $parent->appendElement($tagName, $namespace);

		foreach (Weekday::cases() as $weekday)
		{
			if (!array_key_exists($weekday->value, $this->days))
			{
				continue;
			}

			$element->appendText($weekday->value, $this->days[$weekday->value], $namespace);
		}

		return $element;
	}

	public static function fromXml(XmlElement $xml): static
	{
		$hours = new static();

		foreach (Weekday::cases() as $weekday)
		{
			$value = $xml->text($weekday->value);

			if ($value === null)
			{
				continue;
			}

			if ($value !== '')
			{
				$hours->days[$weekday->value] = $value;
			}
		}

		return $hours;
	}

	/**
	 * @throws InvalidValueException
	 */
	private static function assertRange(Weekday $weekday, string $hours): string
	{
		$hours = trim($hours);
		$pattern = sprintf('#^(?:%1$s|%1$s/%1$s|-|-/-|-/%1$s|%1$s/-)$#', self::RANGE);

		if (preg_match($pattern, $hours) !== 1)
		{
			throw new InvalidValueException($weekday->value, $hours, [
				'HH:MM-HH:MM',
				'HH:MM-HH:MM/HH:MM-HH:MM',
				'-',
				'-/-',
			]);
		}

		return $hours;
	}
}
