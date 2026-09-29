<?php

namespace Webatvantage\Bpost\Api\Shm\DataObjects\Box;

use DateTimeInterface;
use Dom\Element;
use Dom\XMLDocument;
use Webatvantage\Bpost\Api\Contracts\XmlDeserializable;
use Webatvantage\Bpost\Api\Shm\DataObjects\ParcelsDepotAddress;
use Webatvantage\Bpost\Api\Shm\Enums\Product;
use Webatvantage\Bpost\Api\Shm\Support\Xml;
use Webatvantage\Bpost\Api\Support\Assert;

/**
 * Delivery to a parcel locker.
 *
 * The element is at24-7, with the hyphen, which is why it cannot be derived from the class name.
 */
class At247 extends NationalBox implements XmlDeserializable
{
	public private(set) ?string $parcelsDepotId = null;

	public private(set) ?string $parcelsDepotName = null;

	public private(set) ?ParcelsDepotAddress $parcelsDepotAddress = null;

	public private(set) ?Unregistered $unregistered = null;

	public private(set) ?string $receiverName = null;

	public private(set) ?string $receiverCompany = null;

	public private(set) ?string $requestedDeliveryDate = null;

	public function __construct(Product $product = Product::Bpack247)
	{
		$this->product($product);
	}

	public static function allowedProducts(): array
	{
		return [Product::Bpack247, Product::Bpack24hPro];
	}

	/**
	 * The locker's id, from the Geolocator.
	 */
	public function parcelsDepot(string $id, string $name, ParcelsDepotAddress $address): static
	{
		$this->parcelsDepotId = $id;
		$this->parcelsDepotName = $name;
		$this->parcelsDepotAddress = $address;

		return $this;
	}

	/**
	 * How to reach a receiver who is not a registered locker user.
	 */
	public function unregistered(Unregistered $unregistered): static
	{
		$this->unregistered = $unregistered;

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

	public function requestedDeliveryDate(DateTimeInterface $date): static
	{
		$this->requestedDeliveryDate = $date->format('Y-m-d');

		return $this;
	}

	protected function elementName(): string
	{
		return 'at24-7';
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
		Xml::appendText($document, $element, 'parcelsDepotId', $this->parcelsDepotId);
		Xml::appendText($document, $element, 'parcelsDepotName', $this->parcelsDepotName);

		if ($this->parcelsDepotAddress !== null)
		{
			$element->append($this->parcelsDepotAddress->toXml($document));
		}

		if ($this->unregistered !== null)
		{
			$element->append($this->unregistered->toXml($document));
		}

		Xml::appendText($document, $element, 'receiverName', $this->receiverName);
		Xml::appendText($document, $element, 'receiverCompany', $this->receiverCompany);
		Xml::appendText($document, $element, 'requestedDeliveryDate', $this->requestedDeliveryDate);

		return $element;
	}

	public static function fromXml(Element $xml): static
	{
		$box = new static(self::readProduct($xml));
		$box->readCommon($xml);

		$box->parcelsDepotId = Xml::text($xml, 'parcelsDepotId');
		$box->parcelsDepotName = Xml::text($xml, 'parcelsDepotName');

		$parcelsDepotAddress = Xml::child($xml, 'parcelsDepotAddress');

		if ($parcelsDepotAddress !== null)
		{
			$box->parcelsDepotAddress = ParcelsDepotAddress::fromXml($parcelsDepotAddress);
		}

		$unregistered = Xml::child($xml, 'unregistered');

		if ($unregistered !== null)
		{
			$box->unregistered = Unregistered::fromXml($unregistered);
		}

		$receiverName = Xml::text($xml, 'receiverName');

		if ($receiverName !== null)
		{
			$box->receiverName($receiverName);
		}

		$receiverCompany = Xml::text($xml, 'receiverCompany');

		if ($receiverCompany !== null)
		{
			$box->receiverCompany($receiverCompany);
		}

		$requestedDeliveryDate = Xml::text($xml, 'requestedDeliveryDate');

		if ($requestedDeliveryDate !== null)
		{
			$box->requestedDeliveryDate = $requestedDeliveryDate;
		}

		return $box;
	}
}
