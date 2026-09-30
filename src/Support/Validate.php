<?php

namespace Webatvantage\Bpost\Api\Support;

use BackedEnum;
use Closure;
use Webatvantage\Bpost\Api\Exceptions\InvalidLengthException;
use Webatvantage\Bpost\Api\Exceptions\InvalidPatternException;
use Webatvantage\Bpost\Api\Exceptions\InvalidValueException;
use Webatvantage\Bpost\Api\Exceptions\UnexpectedValueException;

/**
 * The field constraints the bpack integration manual documents.
 */
class Validate
{
	public protected(set) static bool $ignoring = false;

	/**
	 * Build a data object from a bpost response without the send-side constraints.
	 *
	 * @template TRead
	 *
	 * @param Closure(): TRead $read
	 *
	 * @return TRead
	 */
	public static function ignoring(Closure $read): mixed
	{
		$previous = static::$ignoring;

		static::$ignoring = true;

		try
		{
			return $read();
		}
		finally
		{
			static::$ignoring = $previous;
		}
	}

	/**
	 * @throws InvalidLengthException
	 */
	public static function maxLength(string $name, string $value, int $max): string
	{
		if (static::$ignoring === false && mb_strlen($value) > $max)
		{
			throw new InvalidLengthException($name, mb_strlen($value), $max);
		}

		return $value;
	}

	/**
	 * @template T of int|float
	 *
	 * @param T $value
	 *
	 * @return T
	 *
	 * @throws InvalidValueException
	 */
	public static function between(string $name, int|float $value, int|float $min, int|float $max): int|float
	{
		if (static::$ignoring === false && ($value < $min || $value > $max))
		{
			throw new InvalidValueException($name, $value, [sprintf('%s to %s', $min, $max)]);
		}

		return $value;
	}

	/**
	 * @throws InvalidValueException
	 */
	public static function atLeast(string $name, int $value, int $min): int
	{
		if (static::$ignoring === false && $value < $min)
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

		if (is_null($case))
		{
			throw new UnexpectedValueException($name, $value, array_map(
				static fn (BackedEnum $case): string|int => $case->value,
				$enum::cases(),
			));
		}

		return $case;
	}

	/**
	 * An address bpost can deliver a message to, within the length the manual documents
	 *
	 * @throws InvalidLengthException
	 * @throws InvalidPatternException
	 */
	public static function email(string $name, string $value, int $max): string
	{
		static::maxLength($name, $value, $max);

		if (static::$ignoring === false && filter_var($value, FILTER_VALIDATE_EMAIL) === false)
		{
			throw new InvalidPatternException($name, $value, 'an email address');
		}

		return $value;
	}

	/**
	 * @throws InvalidValueException
	 */
	public static function countryCode(string $name, string $value): string
	{
		$value = strtoupper($value);

		if (static::$ignoring === false && preg_match('/^[A-Z]{2}$/', $value) !== 1)
		{
			throw new InvalidValueException($name, $value, ['a two-letter ISO country code']);
		}

		return $value;
	}
}
