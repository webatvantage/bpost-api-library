<?php

namespace Webatvantage\Bpost\Api\Shm\DataObjects\Box;

use Webatvantage\Bpost\Api\Exceptions\InvalidLengthException;
use Webatvantage\Bpost\Api\Support\Validate;

/**
 * A note for the shop staff at a Click & Collect point.
 */
class ShopHandlingInstruction
{
	/**
	 * @throws InvalidLengthException
	 */
	public function __construct(public private(set) string $instruction)
	{
		Validate::maxLength('shopHandlingInstruction', $instruction, 50);
	}

	public function __toString(): string
	{
		return $this->instruction;
	}
}
