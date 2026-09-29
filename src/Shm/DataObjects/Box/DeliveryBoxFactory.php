<?php

namespace Webatvantage\Bpost\Api\Shm\DataObjects\Box;

use Webatvantage\Bpost\Api\Exceptions\InvalidLengthException;
use Webatvantage\Bpost\Api\Exceptions\InvalidValueException;
use Webatvantage\Bpost\Api\Exceptions\UnexpectedValueException;
use Webatvantage\Bpost\Api\Support\XmlElement;

/**
 * Builds a delivery method from its element name.
 *
 * Matched explicitly rather than derived from the element name, since at24-7 is not a class name
 * and an unrecognised element should say which element it was.
 */
class DeliveryBoxFactory
{
	/**
	 * @throws InvalidLengthException
	 * @throws InvalidValueException
	 * @throws UnexpectedValueException
	 */
	public static function fromXml(XmlElement $xml): DeliveryBox
	{
		return match ($xml->localName)
		{
			'atHome' => AtHome::fromXml($xml),
			'atBpost' => AtBpost::fromXml($xml),
			'at24-7' => At247::fromXml($xml),
			'international' => International::fromXml($xml),
			'atIntlPugo' => AtIntlPugo::fromXml($xml),
			default => throw new UnexpectedValueException('deliveryMethod', $xml->localName, [
				'atHome',
				'atBpost',
				'at24-7',
				'international',
				'atIntlPugo',
			]),
		};
	}
}
