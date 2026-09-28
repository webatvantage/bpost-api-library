<?php

namespace Webatvantage\Bpost\Api\Exceptions;

final class InvalidLengthException extends LogicException
{
	public function __construct(
		public readonly string $name,
		public readonly int $length,
		public readonly int $maxLength,
	) {
		parent::__construct(sprintf(
			'Invalid length (%d) for "%s", maximum is %d.',
			$length,
			$name,
			$maxLength,
		));
	}
}
