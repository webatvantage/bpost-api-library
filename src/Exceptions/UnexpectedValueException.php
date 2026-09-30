<?php

namespace Webatvantage\Bpost\Api\Exceptions;

use Throwable;

/**
 * A value in bpost's response that this library cannot map onto its model.
 *
 * Distinct from InvalidArgumentException: the caller passed nothing wrong, so there is no input to
 * correct.
 */
class UnexpectedValueException extends BpostException
{
	/**
	 * @param array<array-key, string|int> $known
	 */
	public function __construct(
		public readonly string $name,
		public readonly mixed $value,
		array $known = [],
		?Throwable $previous = null,
	) {
		$message = sprintf('bpost sent an unrecognised value (%s) for "%s".', var_export($value, true), $name);

		if (count($known) !== 0)
		{
			$message = sprintf(
				'bpost sent an unrecognised value (%s) for "%s", known values are: %s.',
				var_export($value, true),
				$name,
				implode(', ', $known),
			);
		}

		parent::__construct($message, 0, $previous);
	}
}
