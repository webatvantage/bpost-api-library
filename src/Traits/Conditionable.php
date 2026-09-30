<?php

namespace Webatvantage\Bpost\Api\Traits;

trait Conditionable
{
	/**
	 * Apply the callback if the given "value" is truthy.
	 *
	 * @param mixed $value
	 * @param callable $callback
	 * @param callable|null $default
	 *
	 * @return static|mixed
	 */
	public function when(mixed $value, callable $callback, mixed $default = null)
	{
		if ($value)
		{
			return $callback($this, $value) ?: $this;
		}
		elseif ($default)
		{
			return $default($this, $value) ?: $this;
		}

		return $this;
	}
}
