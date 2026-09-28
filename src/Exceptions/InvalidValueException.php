<?php

namespace Webatvantage\Bpost\Api\Exceptions;

class InvalidValueException extends InvalidArgumentException
{
	/**
	 * @param string $name
	 * @param mixed $value
	 * @param array<array-key, string|int> $allowed
	 */
	public function __construct(
		public readonly string $name,
		public readonly mixed $value,
		array $allowed = [],
	) {
		$message = sprintf('Invalid value (%s) for "%s".', var_export($value, true), $name);

		if (count($allowed) !== 0)
		{
			$message = sprintf(
				'Invalid value (%s) for "%s", possible values are: %s.',
				var_export($value, true),
				$name,
				implode(', ', $allowed),
			);
		}

		parent::__construct($message);
	}
}
