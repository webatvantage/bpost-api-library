<?php

namespace Webatvantage\Bpost\Api\Shm\DataObjects\Box;

use Webatvantage\Bpost\Api\Contracts\XmlDeserializable;
use Webatvantage\Bpost\Api\Exceptions\InvalidArgumentException;
use Webatvantage\Bpost\Api\Exceptions\InvalidLengthException;
use Webatvantage\Bpost\Api\Exceptions\InvalidValueException;
use Webatvantage\Bpost\Api\Exceptions\UnexpectedValueException;
use Webatvantage\Bpost\Api\Shm\DataObjects\PugoAddress;
use Webatvantage\Bpost\Api\Shm\Enums\Product;
use Webatvantage\Bpost\Api\Support\Validate;
use Webatvantage\Bpost\Api\Support\XmlElement;

/**
 * Delivery to a pick-up point or locker abroad.
 */
class AtIntlPugo extends InternationalBox implements XmlDeserializable
{
	public private(set) ?string $pugoId = null;

	public private(set) ?string $pugoName = null;

	public private(set) ?PugoAddress $pugoAddress = null;

	public private(set) ?string $receiverName = null;

	public private(set) ?string $receiverCompany = null;

	/**
	 * @throws InvalidValueException
	 */
	public function __construct()
	{
		// The only product this delivery method offers.
		$this->product(Product::BpackAtBpostInternational);
	}

	public static function allowedProducts(): array
	{
		return [Product::BpackAtBpostInternational];
	}

	/**
	 * The point's id and name, from the Geolocator's service point details.
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

	protected function elementName(): string
	{
		return 'atIntlPugo';
	}

	/**
	 * @throws InvalidArgumentException
	 */
	protected function buildElement(XmlElement $wrapper): XmlElement
	{
		$namespace = $this->childNamespace();
		$element = $wrapper->appendElement($this->elementName(), $namespace);

		$this->appendShared($element);

		$element->appendText('pugoId', $this->pugoId, $namespace);
		$element->appendText('pugoName', $this->pugoName, $namespace);

		if ($this->pugoAddress !== null)
		{
			$this->pugoAddress->toXml($element);
		}

		$element->appendText('receiverName', $this->receiverName, $namespace);
		$element->appendText('receiverCompany', $this->receiverCompany, $namespace);

		return $element;
	}

	/**
	 * @throws InvalidLengthException
	 * @throws InvalidValueException
	 * @throws UnexpectedValueException
	 */
	public static function fromXml(XmlElement $xml): static
	{
		$box = new static();
		$box->readShared($xml);

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

		return $box;
	}
}
