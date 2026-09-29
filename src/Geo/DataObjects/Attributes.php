<?php

namespace Webatvantage\Bpost\Api\Geo\DataObjects;

use Webatvantage\Bpost\Api\Contracts\XmlDeserializable;
use Webatvantage\Bpost\Api\Geo\Enums\LockerType;
use Webatvantage\Bpost\Api\Support\XmlElement;

/**
 * Locker attributes, returned only when the request asked for them and only for Belgian lockers.
 */
class Attributes implements XmlDeserializable
{
	public function __construct(public readonly ?LockerType $lockerType = null, public readonly ?bool $nightDelivery = null) {}

	public static function fromXml(XmlElement $xml): static
	{
		$lockerType = null;
		$nightDelivery = null;

		foreach ($xml->children('Attribute') as $attribute)
		{
			$value = $attribute->text('TextValue') ?? '';

			match (strtoupper($attribute->text('AttributeCode') ?? ''))
			{
				'LOCKERTYPE' => $lockerType = LockerType::tryFrom(strtoupper($value)),
				'NIGHTDELIVERY' => $nightDelivery = strtoupper($value) === 'TRUE',
				default => null,
			};
		}

		return new static($lockerType, $nightDelivery);
	}

	public function isEmpty(): bool
	{
		return $this->lockerType === null && $this->nightDelivery === null;
	}
}
