<?php

namespace Webatvantage\Bpost\Api\Shm\DataObjects\Box;

use SimpleXMLElement;
use Webatvantage\Bpost\Api\Exceptions\InvalidValueException;

/**
 * Builds a delivery method from its element name.
 *
 * Matched explicitly rather than derived from the element name, since at24-7 is not a class name
 * and an unrecognised element should say which element it was.
 */
class DeliveryBoxFactory
{
	/**
	 * @throws InvalidValueException
	 */
	public static function fromXml(SimpleXMLElement $xml): DeliveryBox
	{
		return match ($xml->getName())
		{
			'atHome' => AtHome::fromXml($xml),
			'atBpost' => AtBpost::fromXml($xml),
			'at24-7' => At247::fromXml($xml),
			'international' => International::fromXml($xml),
			'atIntlPugo' => AtIntlPugo::fromXml($xml),
			default => throw new InvalidValueException('deliveryMethod', $xml->getName(), [
				'atHome',
				'atBpost',
				'at24-7',
				'international',
				'atIntlPugo',
			]),
		};
	}
}
