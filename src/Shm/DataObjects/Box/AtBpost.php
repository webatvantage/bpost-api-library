<?php

namespace Webatvantage\Bpost\Api\Shm\DataObjects\Box;

use DateTimeInterface;
use DOMDocument;
use DOMElement;
use SimpleXMLElement;
use Webatvantage\Bpost\Api\Contracts\XmlDeserializable;
use Webatvantage\Bpost\Api\Shm\DataObjects\PugoAddress;
use Webatvantage\Bpost\Api\Shm\Enums\Product;
use Webatvantage\Bpost\Api\Shm\Support\Assert;
use Webatvantage\Bpost\Api\Shm\Support\Xml;

/**
 * Delivery to a pick-up point: a post office, post point or parcel point.
 *
 * The receiver is named rather than addressed, because the parcel goes to the point.
 */
class AtBpost extends NationalBox implements XmlDeserializable
{
	public private(set) ?string $pugoId = null;

	public private(set) ?string $pugoName = null;

	public private(set) ?PugoAddress $pugoAddress = null;

	public private(set) ?string $receiverName = null;

	public private(set) ?string $receiverCompany = null;

	public private(set) ?OpeningHours $openingHours = null;

	public private(set) ?string $desiredDeliveryPlace = null;

	public private(set) ?ShopHandlingInstruction $shopHandlingInstruction = null;

	public private(set) ?string $requestedDeliveryDate = null;

	public function __construct(Product $product = Product::BpackAtBpost)
	{
		$this->product($product);
	}

	public static function allowedProducts(): array
	{
		return [Product::BpackAtBpost, Product::BpackClickAndCollect];
	}

	/**
	 * The point's id, from the Geolocator.
	 */
	public function pugo(string $id, string $name, PugoAddress $address): static
	{
		$this->pugoId = $id;
		$this->pugoName = $name;
		$this->pugoAddress = $address;

		return $this;
	}

	public function receiverName(string $receiverName): static
	{
		$this->receiverName = Assert::maxLength('receiverName', $receiverName, 40);

		return $this;
	}

	public function receiverCompany(string $receiverCompany): static
	{
		$this->receiverCompany = Assert::maxLength('receiverCompany', $receiverCompany, 40);

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

	public function shopHandlingInstruction(string $instruction): static
	{
		$this->shopHandlingInstruction = new ShopHandlingInstruction($instruction);

		return $this;
	}

	public function requestedDeliveryDate(DateTimeInterface $date): static
	{
		$this->requestedDeliveryDate = $date->format('Y-m-d');

		return $this;
	}

	protected function elementName(): string
	{
		return 'atBpost';
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

		if ($this->openingHours !== null && !$this->openingHours->isEmpty())
		{
			$element->appendChild($this->openingHours->toXml($document));
		}

		Xml::appendText($document, $element, 'desiredDeliveryPlace', $this->desiredDeliveryPlace);
		Xml::appendText($document, $element, 'pugoId', $this->pugoId);
		Xml::appendText($document, $element, 'pugoName', $this->pugoName);

		if ($this->pugoAddress !== null)
		{
			$element->appendChild($this->pugoAddress->toXml($document));
		}

		Xml::appendText($document, $element, 'receiverName', $this->receiverName);
		Xml::appendText($document, $element, 'receiverCompany', $this->receiverCompany);
		Xml::appendText($document, $element, 'shopHandlingInstruction', $this->shopHandlingInstruction?->instruction);
		Xml::appendText($document, $element, 'requestedDeliveryDate', $this->requestedDeliveryDate);

		return $element;
	}

	public static function fromXml(SimpleXMLElement $xml): static
	{
		$box = new static(self::readProduct($xml));
		$box->readCommon($xml);

		if (isset($xml->openingHours))
		{
			$box->openingHours = OpeningHours::fromXml($xml->openingHours);
		}

		if (isset($xml->desiredDeliveryPlace) && trim((string)$xml->desiredDeliveryPlace) !== '')
		{
			$box->desiredDeliveryPlace((string)$xml->desiredDeliveryPlace);
		}

		$box->pugoId = isset($xml->pugoId) ? (string)$xml->pugoId : null;
		$box->pugoName = isset($xml->pugoName) ? (string)$xml->pugoName : null;

		if (isset($xml->pugoAddress))
		{
			$box->pugoAddress = PugoAddress::fromXml(Xml::readChildren($xml->pugoAddress, Xml::READ_COMMON));
		}

		if (isset($xml->receiverName) && trim((string)$xml->receiverName) !== '')
		{
			$box->receiverName((string)$xml->receiverName);
		}

		if (isset($xml->receiverCompany) && trim((string)$xml->receiverCompany) !== '')
		{
			$box->receiverCompany((string)$xml->receiverCompany);
		}

		if (isset($xml->shopHandlingInstruction) && trim((string)$xml->shopHandlingInstruction) !== '')
		{
			$box->shopHandlingInstruction((string)$xml->shopHandlingInstruction);
		}

		if (isset($xml->requestedDeliveryDate) && trim((string)$xml->requestedDeliveryDate) !== '')
		{
			$box->requestedDeliveryDate = trim((string)$xml->requestedDeliveryDate);
		}

		return $box;
	}
}
