<?php

namespace Webatvantage\Bpost\Api\Parcel\DataObjects;

use DateTimeInterface;
use Webatvantage\Bpost\Api\Contracts\XmlDeserializable;
use Webatvantage\Bpost\Api\Exceptions\UnexpectedValueException;
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

	/**
	 * @throws UnexpectedValueException
	 */
	public static function fromXml(XmlElement $xml): static
	{
		return new static(
			time: $xml->dateTime('time'),
			stateCode: $xml->text('stateCode'),
			stateDescription: $xml->text('stateDescription'),
		);
	}
}
