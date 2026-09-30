<?php

namespace Webatvantage\Bpost\Api\Shm\DataObjects\Box;

use DateTimeInterface;
use Webatvantage\Bpost\Api\Contracts\XmlDeserializable;
use Webatvantage\Bpost\Api\DataObjects\OpeningHours;
use Webatvantage\Bpost\Api\Exceptions\InvalidArgumentException;
use Webatvantage\Bpost\Api\Exceptions\InvalidLengthException;
use Webatvantage\Bpost\Api\Exceptions\InvalidValueException;
use Webatvantage\Bpost\Api\Exceptions\UnexpectedValueException;
use Webatvantage\Bpost\Api\Shm\DataObjects\PugoAddress;
use Webatvantage\Bpost\Api\Shm\Enums\Product;
use Webatvantage\Bpost\Api\Support\Validate;
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

	/**
	 * @throws InvalidValueException
	 */
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

		if ($this->openingHours !== null && !$this->openingHours->isEmpty())
		{
			$this->openingHours->toXml($element, $namespace);
		}

		$element->appendText('desiredDeliveryPlace', $this->desiredDeliveryPlace, $namespace);
		$element->appendText('pugoId', $this->pugoId, $namespace);
		$element->appendText('pugoName', $this->pugoName, $namespace);

		$this->pugoAddress?->toXml($element, $namespace);

		$element->appendText('receiverName', $this->receiverName, $namespace);
		$element->appendText('receiverCompany', $this->receiverCompany, $namespace);
		$element->appendText('shopHandlingInstruction', $this->shopHandlingInstruction?->instruction, $namespace);
		$element->appendText('requestedDeliveryDate', $this->requestedDeliveryDate, $namespace);

		return $element;
	}

	/**
	 * @throws InvalidValueException
	 * @throws UnexpectedValueException
	 * @throws InvalidLengthException
	 */
	public static function fromXml(XmlElement $xml): static
	{
		$box = new static(self::readProduct($xml));
		$box->readCommon($xml);

		$openingHours = $xml->child('openingHours');
		$pugoAddress = $xml->child('pugoAddress');
		$shopHandlingInstruction = $xml->text('shopHandlingInstruction');

		$box->openingHours = isset($openingHours) ? OpeningHours::fromXml($openingHours) : null;
		$box->desiredDeliveryPlace = $xml->text('desiredDeliveryPlace');
		$box->pugoId = $xml->text('pugoId');
		$box->pugoName = $xml->text('pugoName');
		$box->pugoAddress = isset($pugoAddress) ? PugoAddress::fromXml($pugoAddress) : null;
		$box->receiverName = $xml->text('receiverName');
		$box->receiverCompany = $xml->text('receiverCompany');
		$box->requestedDeliveryDate = $xml->text('requestedDeliveryDate');

		$box->shopHandlingInstruction = isset($shopHandlingInstruction)
			? Validate::reading(static fn (): ShopHandlingInstruction => new ShopHandlingInstruction($shopHandlingInstruction))
			: null;

		return $box;
	}
}
