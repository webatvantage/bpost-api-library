<?php

namespace Webatvantage\Bpost\Api\Shm\DataObjects\Box;

use DateTimeInterface;
use Webatvantage\Bpost\Api\Contracts\XmlDeserializable;
use Webatvantage\Bpost\Api\DataObjects\OpeningHours;
use Webatvantage\Bpost\Api\Exceptions\InvalidArgumentException;
use Webatvantage\Bpost\Api\Exceptions\InvalidLengthException;
use Webatvantage\Bpost\Api\Exceptions\InvalidValueException;
use Webatvantage\Bpost\Api\Exceptions\UnexpectedValueException;
use Webatvantage\Bpost\Api\Shm\DataObjects\Receiver;
use Webatvantage\Bpost\Api\Shm\Enums\Product;
use Webatvantage\Bpost\Api\Support\Validate;
use Webatvantage\Bpost\Api\Support\XmlElement;

/**
 * Delivery to an address.
 *
 * Opening hours and a desired delivery place are only read for bpack 24h business; dimensions are
 * only read, and are mandatory, for bpack XL.
 */
class AtHome extends NationalBox implements XmlDeserializable
{
	public private(set) ?Receiver $receiver = null;

	public private(set) ?OpeningHours $openingHours = null;

	public private(set) ?string $desiredDeliveryPlace = null;

	public private(set) ?Dimensions $dimensions = null;

	public private(set) ?string $requestedDeliveryDate = null;

	/**
	 * @throws InvalidValueException
	 */
	public function __construct(Product $product)
	{
		$this->product($product);
	}

	public static function allowedProducts(): array
	{
		return [
			Product::Bpack24hPro,
			Product::Bpack24hBusiness,
			Product::BpackBus,
			Product::BpackPallet,
			Product::BpackEasyRetour,
			Product::BpackXL,
		];
	}

	public function receiver(Receiver $receiver): static
	{
		$this->receiver = $receiver;

		return $this;
	}

	public function openingHours(OpeningHours $openingHours): static
	{
		$this->openingHours = $openingHours;

		return $this;
	}

	/**
	 * @throws InvalidLengthException
	 */
	public function desiredDeliveryPlace(string $desiredDeliveryPlace): static
	{
		$this->desiredDeliveryPlace = Validate::maxLength('desiredDeliveryPlace', $desiredDeliveryPlace, 50);

		return $this;
	}

	/**
	 * Mandatory for bpack XL, which is the only product that takes them.
	 *
	 * @throws InvalidValueException
	 */
	public function dimensions(Dimensions $dimensions): static
	{
		if ($this->product !== null && !$this->product->requiresDimensions())
		{
			throw new InvalidValueException('product', $this->product->value, [Product::BpackXL->value]);
		}

		$this->dimensions = $dimensions;

		return $this;
	}

	public function requestedDeliveryDate(DateTimeInterface $date): static
	{
		$this->requestedDeliveryDate = $date->format('Y-m-d');

		return $this;
	}

	protected function elementName(): string
	{
		return 'atHome';
	}

	/**
	 * @throws InvalidArgumentException
	 */
	protected function buildElement(XmlElement $wrapper): XmlElement
	{
		$namespace = $this->childNamespace();
		$element = $wrapper->appendElement($this->elementName(), $namespace);

		$element->appendText('product', $this->product?->value, $namespace);

		$this->appendOptions($element);

		$element->appendText('weight', $this->weight, $namespace);

		$this->dimensions?->appendTo($element, $namespace);

		if ($this->openingHours !== null && !$this->openingHours->isEmpty())
		{
			$this->openingHours->toXml($element, $namespace);
		}

		$element->appendText('desiredDeliveryPlace', $this->desiredDeliveryPlace, $namespace);

		if ($this->receiver !== null)
		{
			$this->receiver->toXml($element, $namespace);
		}

		$element->appendText('requestedDeliveryDate', $this->requestedDeliveryDate, $namespace);

		return $element;
	}

	/**
	 * @throws InvalidValueException
	 * @throws UnexpectedValueException
	 */
	public static function fromXml(XmlElement $xml): static
	{
		$box = new static(self::readProduct($xml));
		$box->readCommon($xml);

		$height = $xml->text('height');
		$length = $xml->text('length');
		$width = $xml->text('width');

		if ($height !== null && $length !== null && $width !== null)
		{
			$box->dimensions = new Dimensions((int)$height, (int)$length, (int)$width);
		}

		$openingHours = $xml->child('openingHours');
		$receiver = $xml->child('receiver');

		$box->openingHours = isset($openingHours) ? OpeningHours::fromXml($openingHours) : null;
		$box->desiredDeliveryPlace = $xml->text('desiredDeliveryPlace');
		$box->receiver = isset($receiver) ? Receiver::fromXml($receiver) : null;
		$box->requestedDeliveryDate = $xml->text('requestedDeliveryDate');

		return $box;
	}
}
