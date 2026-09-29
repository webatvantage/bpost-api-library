<?php

namespace Webatvantage\Bpost\Api\Shm\DataObjects\Box;

use DateTimeInterface;
use Dom\Element;
use Dom\XMLDocument;
use Webatvantage\Bpost\Api\Contracts\XmlDeserializable;
use Webatvantage\Bpost\Api\DataObjects\OpeningHours;
use Webatvantage\Bpost\Api\Exceptions\InvalidValueException;
use Webatvantage\Bpost\Api\Shm\DataObjects\Receiver;
use Webatvantage\Bpost\Api\Shm\Enums\Product;
use Webatvantage\Bpost\Api\Shm\Support\Xml;
use Webatvantage\Bpost\Api\Support\Assert;

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

	public function desiredDeliveryPlace(string $desiredDeliveryPlace): static
	{
		$this->desiredDeliveryPlace = Assert::maxLength('desiredDeliveryPlace', $desiredDeliveryPlace, 50);

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

	protected function buildElement(XMLDocument $document): Element
	{
		$element = Xml::element($document, $this->elementName());

		Xml::appendText($document, $element, 'product', $this->product?->value);

		$options = $this->buildOptions($document);

		if ($options !== null)
		{
			$element->append($options);
		}

		Xml::appendText($document, $element, 'weight', $this->weight);

		$this->dimensions?->appendTo($document, $element);

		if ($this->openingHours !== null && !$this->openingHours->isEmpty())
		{
			$element->append($this->openingHours->toXml($document, namespace: Xml::WRITE_NATIONAL));
		}

		Xml::appendText($document, $element, 'desiredDeliveryPlace', $this->desiredDeliveryPlace);

		if ($this->receiver !== null)
		{
			$element->append($this->receiver->toXml($document));
		}

		Xml::appendText($document, $element, 'requestedDeliveryDate', $this->requestedDeliveryDate);

		return $element;
	}

	public static function fromXml(Element $xml): static
	{
		$box = new static(self::readProduct($xml));
		$box->readCommon($xml);

		$height = Xml::text($xml, 'height');
		$length = Xml::text($xml, 'length');
		$width = Xml::text($xml, 'width');

		if ($height !== null && $length !== null && $width !== null)
		{
			$box->dimensions = new Dimensions((int)$height, (int)$length, (int)$width);
		}

		$openingHours = Xml::child($xml, 'openingHours');

		if ($openingHours !== null)
		{
			$box->openingHours = OpeningHours::fromXml($openingHours);
		}

		$desiredDeliveryPlace = Xml::text($xml, 'desiredDeliveryPlace');

		if ($desiredDeliveryPlace !== null)
		{
			$box->desiredDeliveryPlace($desiredDeliveryPlace);
		}

		$receiver = Xml::child($xml, 'receiver');

		if ($receiver !== null)
		{
			$box->receiver(Receiver::fromXml($receiver));
		}

		$requestedDeliveryDate = Xml::text($xml, 'requestedDeliveryDate');

		if ($requestedDeliveryDate !== null)
		{
			$box->requestedDeliveryDate = $requestedDeliveryDate;
		}

		return $box;
	}
}
