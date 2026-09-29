<?php

namespace Webatvantage\Bpost\Api\Geo\DataObjects;

use ArrayIterator;
use Countable;
use Dom\Element;
use IteratorAggregate;
use Traversable;
use Webatvantage\Bpost\Api\Contracts\XmlDeserializable;
use Webatvantage\Bpost\Api\Enums\Weekday;
use Webatvantage\Bpost\Api\Support\Xml;

/**
 * A week of opening hours.
 *
 * Only returned when the request asked for details (`Info=1`); otherwise it is empty.
 *
 * @implements IteratorAggregate<int, Day>
 */
class OpeningHours implements Countable, IteratorAggregate, XmlDeserializable
{
	/**
	 * @param array<string, Day> $days
	 */
	public function __construct(private readonly array $days = []) {}

	public static function fromXml(Element $xml): static
	{
		$days = [];

		foreach (Weekday::cases() as $weekday)
		{
			$day = Xml::child($xml, $weekday->value);

			if ($day !== null)
			{
				$days[$weekday->value] = Day::fromXml($day);
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
