<?php

namespace Webatvantage\Bpost\Api\Geo\DataObjects;

use Webatvantage\Bpost\Api\Contracts\XmlDeserializable;
use Webatvantage\Bpost\Api\Enums\Weekday;
use Webatvantage\Bpost\Api\Exceptions\UnexpectedValueException;
use Webatvantage\Bpost\Api\Support\XmlElement;

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

	/**
	 * @throws UnexpectedValueException
	 */
	public static function fromXml(XmlElement $xml): static
	{
		$weekday = Weekday::tryFrom(ucfirst(strtolower($xml->localName)));

		if ($weekday === null)
		{
			throw new UnexpectedValueException('weekday', $xml->localName, array_column(Weekday::cases(), 'value'));
		}

		return new static(
			weekday: $weekday,
			amOpen: $xml->text('AMOpen'),
			amClose: $xml->text('AMClose'),
			pmOpen: $xml->text('PMOpen'),
			pmClose: $xml->text('PMClose'),
		);
	}

	public function isClosed(): bool
	{
		return $this->amOpen === null && $this->pmOpen === null;
	}
}
