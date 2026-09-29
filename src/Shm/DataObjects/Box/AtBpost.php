<?php

namespace Webatvantage\Bpost\Api\Shm\DataObjects\Box;

use DateTimeInterface;
use Webatvantage\Bpost\Api\Contracts\XmlDeserializable;
use Webatvantage\Bpost\Api\DataObjects\OpeningHours;
use Webatvantage\Bpost\Api\Shm\DataObjects\PugoAddress;
use Webatvantage\Bpost\Api\Shm\Enums\Product;
use Webatvantage\Bpost\Api\Support\Assert;
use Webatvantage\Bpost\Api\Support\XmlElement;

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

	protected function buildElement(XmlElement $wrapper): XmlElement
	{
		$namespace = $this->childNamespace();
		$element = $wrapper->appendElement($this->elementName(), $namespace);

		$element->appendText('product', $this->product?->value, $namespace);

		$this->appendOptions($element);

		$element->appendText('weight', $this->weight, $namespace);

		if ($this->openingHours !== null && !$this->openingHours->isEmpty())
		{
			$this->openingHours->toXml($element, $namespace);
		}

		$element->appendText('desiredDeliveryPlace', $this->desiredDeliveryPlace, $namespace);
		$element->appendText('pugoId', $this->pugoId, $namespace);
		$element->appendText('pugoName', $this->pugoName, $namespace);

		if ($this->pugoAddress !== null)
		{
			$this->pugoAddress->toXml($element, $namespace);
		}

		$element->appendText('receiverName', $this->receiverName, $namespace);
		$element->appendText('receiverCompany', $this->receiverCompany, $namespace);
		$element->appendText('shopHandlingInstruction', $this->shopHandlingInstruction?->instruction, $namespace);
		$element->appendText('requestedDeliveryDate', $this->requestedDeliveryDate, $namespace);

		return $element;
	}

	public static function fromXml(XmlElement $xml): static
	{
		$box = new static(self::readProduct($xml));
		$box->readCommon($xml);

		$openingHours = $xml->child('openingHours');

		if ($openingHours !== null)
		{
			$box->openingHours = OpeningHours::fromXml($openingHours);
		}

		$desiredDeliveryPlace = $xml->text('desiredDeliveryPlace');

		if ($desiredDeliveryPlace !== null)
		{
			$box->desiredDeliveryPlace($desiredDeliveryPlace);
		}

		$box->pugoId = $xml->text('pugoId');
		$box->pugoName = $xml->text('pugoName');

		$pugoAddress = $xml->child('pugoAddress');

		if ($pugoAddress !== null)
		{
			$box->pugoAddress = PugoAddress::fromXml($pugoAddress);
		}

		$receiverName = $xml->text('receiverName');

		if ($receiverName !== null)
		{
			$box->receiverName($receiverName);
		}

		$receiverCompany = $xml->text('receiverCompany');

		if ($receiverCompany !== null)
		{
			$box->receiverCompany($receiverCompany);
		}

		$shopHandlingInstruction = $xml->text('shopHandlingInstruction');

		if ($shopHandlingInstruction !== null)
		{
			$box->shopHandlingInstruction($shopHandlingInstruction);
		}

		$requestedDeliveryDate = $xml->text('requestedDeliveryDate');

		if ($requestedDeliveryDate !== null)
		{
			$box->requestedDeliveryDate = $requestedDeliveryDate;
		}

		return $box;
	}
}
