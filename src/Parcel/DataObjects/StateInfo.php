<?php

namespace Webatvantage\Bpost\Api\Parcel\DataObjects;

use DateTimeImmutable;
use DateTimeInterface;
use Webatvantage\Bpost\Api\Contracts\XmlDeserializable;
use Webatvantage\Bpost\Api\Support\XmlElement;

/**
 * One scan in a parcel's history.
 *
 * The state codes are listed in manual section E.6.2; they are left as strings because bpost adds
 * to the list without notice.
 */
class StateInfo implements XmlDeserializable
{
	public function __construct(
		public private(set) ?DateTimeInterface $time = null,
		public private(set) ?string $stateCode = null,
		public private(set) ?string $stateDescription = null,
	) {}

	public static function fromXml(XmlElement $xml): static
	{
		$time = $xml->text('time') ?? '';

		return new static(
			$time === '' ? null : new DateTimeImmutable($time),
			$xml->text('stateCode'),
			$xml->text('stateDescription'),
		);
	}
}
