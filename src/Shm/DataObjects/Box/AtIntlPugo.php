<?php

namespace Webatvantage\Bpost\Api\Shm\DataObjects\Box;

use DOMDocument;
use DOMElement;
use SimpleXMLElement;
use Webatvantage\Bpost\Api\Contracts\XmlDeserializable;
use Webatvantage\Bpost\Api\Shm\DataObjects\PugoAddress;
use Webatvantage\Bpost\Api\Shm\Enums\Product;
use Webatvantage\Bpost\Api\Shm\Support\Assert;
use Webatvantage\Bpost\Api\Shm\Support\Xml;

/**
 * Delivery to a pick-up point or locker abroad.
 *
 * 3.x could parse one of these but never send one: its toXML() called two methods that do not
 * exist anywhere in the library, so building this box was an unconditional fatal error. No test
 * caught it because none of them called toXML.
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

	protected function buildElement(DOMDocument $document): DOMElement
	{
		$prefix = $this->childPrefix();
		$element = $document->createElement(Xml::prefixed($this->elementName(), $prefix));

		$this->appendShared($document, $element);

		Xml::appendText($document, $element, 'pugoId', $this->pugoId, $prefix);
		Xml::appendText($document, $element, 'pugoName', $this->pugoName, $prefix);

		if ($this->pugoAddress !== null)
		{
			$element->appendChild($this->pugoAddress->toXml($document));
		}

		Xml::appendText($document, $element, 'receiverName', $this->receiverName, $prefix);
		Xml::appendText($document, $element, 'receiverCompany', $this->receiverCompany, $prefix);

		return $element;
	}

	public static function fromXml(SimpleXMLElement $xml): static
	{
		$box = new static();
		$box->readShared($xml);

		$box->pugoId = isset($xml->pugoId) ? (string)$xml->pugoId : null;
		$box->pugoName = isset($xml->pugoName) ? (string)$xml->pugoName : null;

		if (isset($xml->pugoAddress))
		{
			$box->pugoAddress = PugoAddress::fromXml(Xml::readChildren($xml->pugoAddress, Xml::READ_COMMON));
		}

		// 3.x declared both of these and never read them back.
		if (isset($xml->receiverName) && trim((string)$xml->receiverName) !== '')
		{
			$box->receiverName((string)$xml->receiverName);
		}

		if (isset($xml->receiverCompany) && trim((string)$xml->receiverCompany) !== '')
		{
			$box->receiverCompany((string)$xml->receiverCompany);
		}

		return $box;
	}
}
