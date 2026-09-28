<?php

namespace Webatvantage\Bpost\Api\Geo\DataObjects;

use ArrayIterator;
use Countable;
use IteratorAggregate;
use SimpleXMLElement;
use Traversable;
use Webatvantage\Bpost\Api\Contracts\XmlDeserializable;
use Webatvantage\Bpost\Api\Geo\Enums\Weekday;

/**
 * A week of opening hours.
 *
 * Only returned when the request asked for details (`Info=1`); otherwise it is empty.
 *
 * @implements IteratorAggregate<int, Day>
 */
final class OpeningHours implements Countable, IteratorAggregate, XmlDeserializable
{
	/**
	 * @param array<string, Day> $days
	 */
	public function __construct(private readonly array $days = []) {}

	public static function fromXml(SimpleXMLElement $xml): static
	{
		$days = [];

		foreach (Weekday::cases() as $weekday)
		{
			if (isset($xml->{$weekday->value}))
			{
				$days[$weekday->value] = Day::fromXml($xml->{$weekday->value});
			}
		}

		return new static($days);
	}

	public function for(Weekday $weekday): ?Day
	{
		return $this->days[$weekday->value] ?? null;
	}

	/**
	 * @return array<string, Day>
	 */
	public function all(): array
	{
		return $this->days;
	}

	public function count(): int
	{
		return count($this->days);
	}

	public function getIterator(): Traversable
	{
		return new ArrayIterator(array_values($this->days));
	}
}
