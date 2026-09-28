<?php

namespace Webatvantage\Bpost\Api\Geo\Enums;

enum LockerType: string
{
	case Classic = 'CLASSIC';
	case LeanLocker = 'LEAN LOCKER';

	/**
	 * The spelling AttributeFilter expects.
	 *
	 * bpost returns "LEAN LOCKER" with a space in the response but only accepts "LEANLOCKER"
	 * without one when filtering, so the two spellings cannot be the same string.
	 */
	public function filterValue(): string
	{
		return str_replace(' ', '', $this->value);
	}
}
