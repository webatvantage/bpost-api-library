<?php

namespace Webatvantage\Bpost\Api\Shm\DataObjects\Box;

use DateTimeInterface;
use Webatvantage\Bpost\Api\Contracts\XmlDeserializable;
use Webatvantage\Bpost\Api\Exceptions\InvalidArgumentException;
use Webatvantage\Bpost\Api\Exceptions\InvalidLengthException;
use Webatvantage\Bpost\Api\Exceptions\InvalidValueException;
use Webatvantage\Bpost\Api\Exceptions\UnexpectedValueException;
use Webatvantage\Bpost\Api\Shm\DataObjects\ParcelsDepotAddress;
use Webatvantage\Bpost\Api\Shm\Enums\Product;
use Webatvantage\Bpost\Api\Support\Validate;
use Webatvantage\Bpost\Api\Support\XmlElement;

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

	/**
	 * @throws InvalidValueException
	 */
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

	/**
	 * @throws InvalidLengthException
	 */
	public function receiverName(string $receiverName): static
	{
		$this->receiverName = Validate::maxLength('receiverName', $receiverName, 40);

		return $this;
	}

	/**
	 * @throws InvalidLengthException
	 */
	public function receiverCompany(string $receiverCompany): static
	{
		$this->receiverCompany = Validate::maxLength('receiverCompany', $receiverCompany, 40);

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
		$element->appendText('parcelsDepotId', $this->parcelsDepotId, $namespace);
		$element->appendText('parcelsDepotName', $this->parcelsDepotName, $namespace);

		if ($this->parcelsDepotAddress !== null)
		{
			$this->parcelsDepotAddress->toXml($element, $namespace);
		}

		if ($this->unregistered !== null)
		{
			$this->unregistered->toXml($element, $namespace);
		}

		$element->appendText('receiverName', $this->receiverName, $namespace);
		$element->appendText('receiverCompany', $this->receiverCompany, $namespace);
		$element->appendText('requestedDeliveryDate', $this->requestedDeliveryDate, $namespace);

		return $element;
	}

	/**
	 * @throws UnexpectedValueException
	 * @throws InvalidValueException
	 */
	public static function fromXml(XmlElement $xml): static
	{
		$box = new static(self::readProduct($xml));
		$box->readCommon($xml);

		$parcelsDepotAddress = $xml->child('parcelsDepotAddress');
		$unregistered = $xml->child('unregistered');

		$box->parcelsDepotId = $xml->text('parcelsDepotId');
		$box->parcelsDepotName = $xml->text('parcelsDepotName');
		$box->parcelsDepotAddress = isset($parcelsDepotAddress) ? ParcelsDepotAddress::fromXml($parcelsDepotAddress) : null;
		$box->unregistered = isset($unregistered) ? Unregistered::fromXml($unregistered) : null;
		$box->receiverName = $xml->text('receiverName');
		$box->receiverCompany = $xml->text('receiverCompany');
		$box->requestedDeliveryDate = $xml->text('requestedDeliveryDate');

		return $box;
	}
}
