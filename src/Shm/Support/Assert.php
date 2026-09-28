<?php

namespace Webatvantage\Bpost\Api\Shm\Support;

use Webatvantage\Bpost\Api\Exceptions\InvalidLengthException;
use Webatvantage\Bpost\Api\Exceptions\InvalidValueException;

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
