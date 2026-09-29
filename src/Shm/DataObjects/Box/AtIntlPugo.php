<?php

namespace Webatvantage\Bpost\Api\Shm\DataObjects\Box;

use Dom\Element;
use Dom\XMLDocument;
use Webatvantage\Bpost\Api\Contracts\XmlDeserializable;
use Webatvantage\Bpost\Api\Shm\DataObjects\PugoAddress;
use Webatvantage\Bpost\Api\Shm\Enums\Product;
use Webatvantage\Bpost\Api\Shm\Support\Xml;
use Webatvantage\Bpost\Api\Support\Assert;

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

	protected function elementName(): string
	{
		return 'atIntlPugo';
	}

	protected function buildElement(XMLDocument $document): Element
	{
		$prefix = $this->childPrefix();
		$element = Xml::element($document, $this->elementName(), $prefix);

		$this->appendShared($document, $element);

		Xml::appendText($document, $element, 'pugoId', $this->pugoId, $prefix);
		Xml::appendText($document, $element, 'pugoName', $this->pugoName, $prefix);

		if ($this->pugoAddress !== null)
		{
			$element->append($this->pugoAddress->toXml($document));
		}

		Xml::appendText($document, $element, 'receiverName', $this->receiverName, $prefix);
		Xml::appendText($document, $element, 'receiverCompany', $this->receiverCompany, $prefix);

		return $element;
	}

	public static function fromXml(Element $xml): static
	{
		$box = new static();
		$box->readShared($xml);

		$box->pugoId = Xml::text($xml, 'pugoId');
		$box->pugoName = Xml::text($xml, 'pugoName');

		$pugoAddress = Xml::child($xml, 'pugoAddress');

		if ($pugoAddress !== null)
		{
			$box->pugoAddress = PugoAddress::fromXml($pugoAddress);
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

		return $box;
	}
}
