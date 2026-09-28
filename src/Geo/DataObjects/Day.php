<?php

namespace Webatvantage\Bpost\Api\Geo\DataObjects;

use SimpleXMLElement;
use Webatvantage\Bpost\Api\Contracts\XmlDeserializable;
use Webatvantage\Bpost\Api\Enums\Weekday;
use Webatvantage\Bpost\Api\Exceptions\InvalidValueException;

/**
 * One day's opening hours, as two optional ranges.
 *
 * A point that closes at midday has an AM pair and no PM pair; a point closed all day has neither.
 */
class Day implements XmlDeserializable
{
	public function __construct(
		public readonly Weekday $weekday,
		public readonly ?string $amOpen = null,
		public readonly ?string $amClose = null,
		public readonly ?string $pmOpen = null,
		public readonly ?string $pmClose = null,
	) {}

	public static function fromXml(SimpleXMLElement $xml): static
	{
		$weekday = Weekday::tryFrom(ucfirst(strtolower($xml->getName())));

		if ($weekday === null)
		{
			throw new InvalidValueException('weekday', $xml->getName(), array_column(Weekday::cases(), 'value'));
		}

		return new static(
			$weekday,
			self::value($xml, 'AMOpen'),
			self::value($xml, 'AMClose'),
			self::value($xml, 'PMOpen'),
			self::value($xml, 'PMClose'),
		);
	}

	public function isClosed(): bool
	{
		return $this->amOpen === null && $this->pmOpen === null;
	}

	private static function value(SimpleXMLElement $xml, string $name): ?string
	{
		if (!isset($xml->{$name}))
		{
			return null;
		}

		$value = trim((string)$xml->{$name});

		return $value === '' ? null : $value;
	}
}
