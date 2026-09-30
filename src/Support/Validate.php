<?php

namespace Webatvantage\Bpost\Api\Support;

use BackedEnum;
use Webatvantage\Bpost\Api\Exceptions\InvalidLengthException;
use Webatvantage\Bpost\Api\Exceptions\InvalidPatternException;
use Webatvantage\Bpost\Api\Exceptions\InvalidValueException;
use Webatvantage\Bpost\Api\Exceptions\UnexpectedValueException;

/**
 * The field constraints the bpack integration manual documents.
 *
 * Checked here rather than left to bpost because the API answers a rejected field with a schema
 * violation that does not say which value was at fault.
 *
 * These are rules about what may be *sent*. A record bpost already holds is reported as it is,
 * however far outside them it falls, so a fromXml() that cannot avoid a constructor's checks runs
 * inside reading() instead. Note enum() is not suspended: a case this library does not know is a
 * fact about the response, not a rule about the request.
 */
class Validate
{
	private static int $reading = 0;

	/**
	 * Build a data object from a bpost response without the send-side constraints.
	 *
	 * @template TRead
	 *
	 * @param callable(): TRead $read
	 *
	 * @return TRead
	 */
	public static function reading(callable $read): mixed
	{
		self::$reading++;

		try
		{
			return $read();
		}
		finally
		{
			self::$reading--;
		}
	}

	/**
	 * @throws InvalidLengthException
	 */
	public static function maxLength(string $name, string $value, int $max): string
	{
		if (self::$reading === 0 && mb_strlen($value) > $max)
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
		if (self::$reading === 0 && ($value < $min || $value > $max))
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
		if (self::$reading === 0 && $value < $min)
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
	 * An address bpost can deliver a message to, within the length the manual documents.
	 *
	 * bpost documents a length and no format, and answers a malformed address by accepting the
	 * order and then never sending the notification, so a typo is invisible until a customer asks
	 * where their parcel is. filter_var is stricter than RFC 5322 — an internationalised domain is
	 * refused — so an address it rejects has to be corrected rather than passed through.
	 *
	 * @throws InvalidLengthException
	 * @throws InvalidPatternException
	 */
	public static function email(string $name, string $value, int $max): string
	{
		self::maxLength($name, $value, $max);

		if (self::$reading === 0 && filter_var($value, FILTER_VALIDATE_EMAIL) === false)
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

		if (self::$reading === 0 && mb_strlen($value) !== 2)
		{
			throw new InvalidValueException($name, $value, ['a two-letter ISO country code']);
		}

		return $value;
	}
}
