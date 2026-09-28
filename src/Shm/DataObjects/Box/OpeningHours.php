<?php

namespace Webatvantage\Bpost\Api\Shm\DataObjects\Box;

use DOMDocument;
use DOMElement;
use SimpleXMLElement;
use Webatvantage\Bpost\Api\Contracts\XmlDeserializable;
use Webatvantage\Bpost\Api\Contracts\XmlSerializable;
use Webatvantage\Bpost\Api\Enums\Weekday;
use Webatvantage\Bpost\Api\Exceptions\InvalidValueException;
use Webatvantage\Bpost\Api\Shm\Support\Xml;

/**
 * When the receiving business is open, for products delivered to an address.
 *
 * Each day is one string rather than a structure, because that is how bpost spells it:
 * "09:00-17:00" for one range, "09:00-12:00/13:00-17:30" for two, "-/-" or "-" for closed, and an
 * empty value for unknown. Unlike the Geolocator's opening hours, which are four separate
 * elements per day.
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

	public function toXml(DOMDocument $document, ?string $prefix = null): DOMElement
	{
		$element = $document->createElement(Xml::prefixed('openingHours', $prefix));

		foreach (Weekday::cases() as $weekday)
		{
			if (!array_key_exists($weekday->value, $this->days))
			{
				continue;
			}

			Xml::appendText($document, $element, $weekday->value, $this->days[$weekday->value], $prefix);
		}

		return $element;
	}

	public static function fromXml(SimpleXMLElement $xml): static
	{
		$hours = new static();

		foreach (Weekday::cases() as $weekday)
		{
			if (!isset($xml->{$weekday->value}))
			{
				continue;
			}

			$value = trim((string)$xml->{$weekday->value});

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
