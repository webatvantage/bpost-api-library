<?php

namespace Webatvantage\Bpost\Api\Geo\DataObjects;

use SimpleXMLElement;
use Webatvantage\Bpost\Api\Contracts\XmlDeserializable;
use Webatvantage\Bpost\Api\Geo\Enums\LockerType;

/**
 * Locker attributes, returned only when the request asked for them and only for Belgian lockers.
 */
class Attributes implements XmlDeserializable
{
	public function __construct(public readonly ?LockerType $lockerType = null, public readonly ?bool $nightDelivery = null) {}

	public static function fromXml(SimpleXMLElement $xml): static
	{
		$lockerType = null;
		$nightDelivery = null;

		foreach ($xml->Attribute ?? [] as $attribute)
		{
			$value = trim((string)$attribute->TextValue);

			match (strtoupper(trim((string)$attribute->AttributeCode)))
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
