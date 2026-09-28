<?php

namespace Webatvantage\Bpost\Api\Shm\DataObjects\Box;

use Webatvantage\Bpost\Api\Support\Assert;

/**
 * A note for the shop staff at a Click & Collect point.
 */
class ShopHandlingInstruction
{
	public function __construct(public private(set) string $instruction)
	{
		Assert::maxLength('shopHandlingInstruction', $instruction, 50);
	}

	public function __toString(): string
	{
		return $this->instruction;
	}
}
