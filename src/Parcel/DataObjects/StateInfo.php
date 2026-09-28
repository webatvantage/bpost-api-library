<?php

namespace Webatvantage\Bpost\Api\Parcel\DataObjects;

use DateTimeImmutable;
use DateTimeInterface;
use SimpleXMLElement;
use Webatvantage\Bpost\Api\Contracts\XmlDeserializable;

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

	public static function fromXml(SimpleXMLElement $xml): static
	{
		$time = trim((string)($xml->time ?? ''));

		return new static(
			$time === '' ? null : new DateTimeImmutable($time),
			isset($xml->stateCode) ? trim((string)$xml->stateCode) : null,
			isset($xml->stateDescription) ? trim((string)$xml->stateDescription) : null,
		);
	}
}
