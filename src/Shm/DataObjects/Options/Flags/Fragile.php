<?php

namespace Webatvantage\Bpost\Api\Shm\DataObjects\Options\Flags;

/**
 * Mark the parcel fragile, which prints a glass-and-exclamation-mark icon on the label.
 *
 * Only valid with bpack XL. bpost adds basic warranty automatically when it is used.
 */
class Fragile extends ShmFlag
{
	protected function tagName(): string
	{
		return 'fragile';
	}
}
