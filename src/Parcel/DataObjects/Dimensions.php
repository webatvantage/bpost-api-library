<?php

namespace Webatvantage\Bpost\Api\Parcel\DataObjects;

use Webatvantage\Bpost\Api\Contracts\XmlDeserializable;
use Webatvantage\Bpost\Api\Contracts\XmlNamespace;
use Webatvantage\Bpost\Api\Contracts\XmlSerializable;
use Webatvantage\Bpost\Api\Exceptions\InvalidArgumentException;
use Webatvantage\Bpost\Api\Exceptions\InvalidValueException;
use Webatvantage\Bpost\Api\Parcel\Enums\ParcelNamespace;
use Webatvantage\Bpost\Api\Support\Validate;
use Webatvantage\Bpost\Api\Support\XmlElement;

/**
 * Parcel dimensions in millimetres. Mandatory for bpack XL.
 */
class Dimensions implements XmlDeserializable, XmlSerializable
{
	/**
	 * @throws InvalidValueException
	 */
	public function __construct(
		public private(set) int $widthInMm,
		public private(set) int $heightInMm,
		public private(set) int $lengthInMm,
	) {
		Validate::between('widthInMm', $widthInMm, 1, 9999);
		Validate::between('heightInMm', $heightInMm, 1, 9999);
		Validate::between('lengthInMm', $lengthInMm, 1, 9999);
	}

	/**
	 * @throws InvalidArgumentException
	 */
	public function toXml(XmlElement $parent, ?XmlNamespace $namespace = ParcelNamespace::Announcement): XmlElement
	{
		$element = $parent->appendElement('dimensions', $namespace);

		$element->appendText('widthInMm', $this->widthInMm, ParcelNamespace::Common);
		$element->appendText('heightInMm', $this->heightInMm, ParcelNamespace::Common);
		$element->appendText('lengthInMm', $this->lengthInMm, ParcelNamespace::Common);

		return $element;
	}

	/**
	 * @throws InvalidValueException
	 */
	public static function fromXml(XmlElement $xml): static
	{
		return Validate::reading(static fn (): static => new static(
			(int)$xml->text('widthInMm'),
			(int)$xml->text('heightInMm'),
			(int)$xml->text('lengthInMm'),
		));
	}
}
