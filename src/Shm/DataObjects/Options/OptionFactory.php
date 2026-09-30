<?php

namespace Webatvantage\Bpost\Api\Shm\DataObjects\Options;

use Webatvantage\Bpost\Api\Contracts\Option;
use Webatvantage\Bpost\Api\Exceptions\UnexpectedValueException;
use Webatvantage\Bpost\Api\Shm\DataObjects\Options\Flags\AutomaticSecondPresentation;
use Webatvantage\Bpost\Api\Shm\DataObjects\Options\Flags\Fragile;
use Webatvantage\Bpost\Api\Shm\DataObjects\Options\Flags\SaturdayDelivery;
use Webatvantage\Bpost\Api\Shm\DataObjects\Options\Flags\Signed;
use Webatvantage\Bpost\Api\Shm\Enums\MessagingType;
use Webatvantage\Bpost\Api\Support\XmlElement;

/**
 * Builds an option from its element name.
 *
 * One factory for every box type, national and international alike, so the two cannot drift.
 */
class OptionFactory
{
	/**
	 * @throws UnexpectedValueException
	 */
	public static function fromXml(XmlElement $xml): Option
	{
		$name = $xml->localName;

		if (MessagingType::tryFrom($name) !== null)
		{
			return Messaging::fromXml($xml);
		}

		return match ($name)
		{
			'cod' => CashOnDelivery::fromXml($xml),
			'insured' => Insured::fromXml($xml),
			'signed' => new Signed(),
			'saturdayDelivery' => new SaturdayDelivery(),
			'automaticSecondPresentation' => new AutomaticSecondPresentation(),
			'fragile' => new Fragile(),
			default => throw new UnexpectedValueException('option', $name, static::knownNames()),
		};
	}

	/**
	 * @return array<int, string>
	 */
	protected static function knownNames(): array
	{
		return [
			...array_column(MessagingType::cases(), 'value'),
			'cod',
			'insured',
			'signed',
			'saturdayDelivery',
			'automaticSecondPresentation',
			'fragile',
		];
	}
}
