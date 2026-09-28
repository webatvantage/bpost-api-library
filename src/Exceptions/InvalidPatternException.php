<?php

namespace Webatvantage\Bpost\Api\Exceptions;

class InvalidPatternException extends LogicException
{
	public function __construct(
		public readonly string $name,
		public readonly string $value,
		public readonly string $pattern,
	) {
		parent::__construct(sprintf(
			'Invalid value (%s) for "%s", pattern is: "%s".',
			$value,
			$name,
			$pattern,
		));
	}
}
