<?php

namespace Webatvantage\Bpost\Api\Shm\Enums;

/**
 * The statuses a box can be in.
 *
 * The first five are Shipping Manager's own, before the parcel is handed over; the rest are
 * operational and only appear on a retrieved order. Note bpost hyphenates ON-HOLD and underscores
 * the others.
 */
enum BoxStatus: string
{
	case Pending = 'PENDING';
	case Open = 'OPEN';
	case Cancelled = 'CANCELLED';
	case OnHold = 'ON-HOLD';
	case Printed = 'PRINTED';
	case Announced = 'ANNOUNCED';
	case InTransit = 'IN_TRANSIT';
	case AwaitingPickup = 'AWAITING_PICKUP';
	case Delivered = 'DELIVERED';
	case BackToSender = 'BACK_TO_SENDER';

	/**
	 * The three an Update Order Status call accepts; the rest are bpost's to set.
	 */
	public function isSettable(): bool
	{
		return in_array($this, [self::Open, self::Cancelled, self::OnHold], true);
	}
}
