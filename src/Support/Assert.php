<?php

namespace Webatvantage\Bpost\Api\Support;

use BackedEnum;
use Webatvantage\Bpost\Api\Exceptions\InvalidLengthException;
use Webatvantage\Bpost\Api\Exceptions\InvalidValueException;
use Webatvantage\Bpost\Api\Exceptions\UnexpectedValueException;

/**
 * The field constraints the bpack integration manual documents.
 *
 * Checked here rather than left to bpost because the API answers a rejected field with a schema
 * violation that does not say which value was at fault.
 */
class Assert
{
	/**
	 * @throws InvalidLengthException
	 */
	public static function maxLength(string $name, string $value, int $max): string
	{
		if (mb_strlen($value) > $max)
		{
			throw new InvalidLengthException($name, mb_strlen($value), $max);
		}

		return $value;
	}

	/**
	 * @throws InvalidValueException
	 */
	public static function between(string $name, int $value, int $min, int $max): int
	{
		if ($value < $min || $value > $max)
		{
			throw new InvalidValueException($name, $value, [sprintf('%d to %d', $min, $max)]);
		}

		return $value;
	}

	/**
	 * @throws InvalidValueException
	 */
	public static function atLeast(string $name, int $value, int $min): int
	{
		if ($value < $min)
		{
			throw new InvalidValueException($name, $value, [sprintf('%d or more', $min)]);
		}

		return $value;
	}

	/**
	 * @template T of BackedEnum
	 *
	 * @param class-string<T> $enum
	 *
	 * @return T
	 *
	 * @throws UnexpectedValueException
	 */
	public static function enum(string $name, string $enum, string|int $value): BackedEnum
	{
		$case = $enum::tryFrom($value);

		if ($case === null)
		{
			throw new UnexpectedValueException($name, $value, array_map(
				static fn (BackedEnum $case): string|int => $case->value,
				$enum::cases(),
			));
		}

		return $case;
	}

	/**
	 * @throws InvalidValueException
	 */
	public static function countryCode(string $name, string $value): string
	{
		$value = strtoupper($value);

		if (mb_strlen($value) !== 2)
		{
			throw new InvalidValueException($name, $value, ['a two-letter ISO country code']);
		}

		return $value;
	}
}
