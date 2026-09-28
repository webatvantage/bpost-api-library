<?php

namespace Webatvantage\Bpost\Api\Shm\DataObjects\Box;

use SimpleXMLElement;
use Webatvantage\Bpost\Api\Exceptions\InvalidValueException;

/**
 * Builds a delivery method from its element name.
 *
 * 3.x derived the class name from the element with ucfirst(), which is why at24-7 needed a special
 * case and why an unrecognised element produced a "class not found" style failure rather than a
 * message naming the element.
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
