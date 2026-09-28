<?php

namespace Webatvantage\Bpost\Api\Shm\DataObjects\Box;

use DateTimeInterface;
use DOMDocument;
use DOMElement;
use SimpleXMLElement;
use Webatvantage\Bpost\Api\Contracts\XmlDeserializable;
use Webatvantage\Bpost\Api\Exceptions\InvalidValueException;
use Webatvantage\Bpost\Api\Shm\DataObjects\Receiver;
use Webatvantage\Bpost\Api\Shm\Enums\Product;
use Webatvantage\Bpost\Api\Shm\Support\Assert;
use Webatvantage\Bpost\Api\Shm\Support\Xml;

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

	protected function buildElement(DOMDocument $document): DOMElement
	{
		$element = $document->createElement($this->elementName());

		Xml::appendText($document, $element, 'product', $this->product?->value);

		$options = $this->buildOptions($document);

		if ($options !== null)
		{
			$element->appendChild($options);
		}

		Xml::appendText($document, $element, 'weight', $this->weight);

		$this->dimensions?->appendTo($document, $element);

		if ($this->openingHours !== null && !$this->openingHours->isEmpty())
		{
			$element->appendChild($this->openingHours->toXml($document));
		}

		Xml::appendText($document, $element, 'desiredDeliveryPlace', $this->desiredDeliveryPlace);

		if ($this->receiver !== null)
		{
			$element->appendChild($this->receiver->toXml($document));
		}

		Xml::appendText($document, $element, 'requestedDeliveryDate', $this->requestedDeliveryDate);

		return $element;
	}

	public static function fromXml(SimpleXMLElement $xml): static
	{
		$box = new static(self::readProduct($xml));
		$box->readCommon($xml);

		if (isset($xml->height, $xml->length, $xml->width))
		{
			$box->dimensions = new Dimensions((int)$xml->height, (int)$xml->length, (int)$xml->width);
		}

		if (isset($xml->openingHours))
		{
			$box->openingHours = OpeningHours::fromXml($xml->openingHours);
		}

		if (isset($xml->desiredDeliveryPlace) && trim((string)$xml->desiredDeliveryPlace) !== '')
		{
			$box->desiredDeliveryPlace((string)$xml->desiredDeliveryPlace);
		}

		if (isset($xml->receiver))
		{
			$box->receiver(Receiver::fromXml(Xml::readChildren($xml->receiver, Xml::READ_COMMON)));
		}

		if (isset($xml->requestedDeliveryDate) && trim((string)$xml->requestedDeliveryDate) !== '')
		{
			$box->requestedDeliveryDate = trim((string)$xml->requestedDeliveryDate);
		}

		return $box;
	}
}
