<?php

namespace Webatvantage\Bpost\Api\Tests\Geo\Enums;

use Webatvantage\Bpost\Api\Geo\Enums\LockerType;
use Webatvantage\Bpost\Api\Geo\Enums\PointType;
use Webatvantage\Bpost\Api\Tests\TestCase;

class PointTypeTest extends TestCase
{
	/**
	 * The manual's own worked example: post offices + post points + parcel points is Type 19.
	 */
	public function test_it_combines_types_the_way_the_manual_documents()
	{
		$this->assertSame(19, PointType::mask(
			PointType::PostOffice,
			PointType::PostPoint,
			PointType::ParcelPoint,
		));

		$this->assertSame(23, PointType::mask(
			PointType::PostOffice,
			PointType::PostPoint,
			PointType::ParcelLocker,
			PointType::ParcelPoint,
		));
	}

	public function test_it_splits_a_mask_back_into_types()
	{
		$this->assertSame(
			[PointType::PostOffice, PointType::PostPoint, PointType::ParcelPoint],
			PointType::fromMask(19),
		);
	}

	public function test_an_empty_mask_is_zero()
	{
		$this->assertSame(0, PointType::mask());
		$this->assertSame([], PointType::fromMask(0));
	}

	/**
	 * bpost returns "LEAN LOCKER" but only accepts "LEANLOCKER" in AttributeFilter.
	 */
	public function test_locker_type_has_a_separate_filter_spelling()
	{
		$this->assertSame('LEAN LOCKER', LockerType::LeanLocker->value);
		$this->assertSame('LEANLOCKER', LockerType::LeanLocker->filterValue());
		$this->assertSame('CLASSIC', LockerType::Classic->filterValue());
	}
}
