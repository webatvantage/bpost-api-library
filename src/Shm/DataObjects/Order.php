<?php

namespace Webatvantage\Bpost\Api\Shm\DataObjects;

use DOMDocument;
use DOMElement;
use SimpleXMLElement;
use Webatvantage\Bpost\Api\Contracts\XmlDeserializable;
use Webatvantage\Bpost\Api\Shm\Support\Xml;
use Webatvantage\Bpost\Api\Support\Assert;

/**
 * A Shipping Manager order: one reference, and the parcels sent under it.
 *
 * Posting an order whose reference already exists adds boxes to it rather than replacing it, and
 * leaves the existing boxes alone.
 */
class Order implements XmlDeserializable
{
	public private(set) ?string $costCenter = null;

	/** @var array<int, OrderLine> */
	public private(set) array $lines = [];

	/** @var array<int, Box> */
	public private(set) array $boxes = [];

	public function __construct(public private(set) string $reference)
	{
		Assert::maxLength('reference', $reference, 50);
	}

	/**
	 * Groups barcodes on the invoice. bpost does not allow a unique value per barcode.
	 */
	public function costCenter(string $costCenter): static
	{
		$this->costCenter = Assert::maxLength('costCenter', $costCenter, 50);

		return $this;
	}

	public function addLine(string $text, int $numberOfItems): static
	{
		$this->lines[] = new OrderLine($text, $numberOfItems);

		return $this;
	}

	public function addBox(Box $box): static
	{
		$this->boxes[] = $box;

		return $this;
	}

	/**
	 * @param string $accountId Written into the document, and it must match the account the
	 *                          request authenticates as
	 */
	public function toXml(DOMDocument $document, string $accountId): DOMElement
	{
		$order = $document->createElement(Xml::prefixed('order', Xml::PREFIX_GLOBAL));
		Xml::declareNamespaces($order);

		Xml::appendText($document, $order, 'accountId', $accountId, Xml::PREFIX_GLOBAL);
		Xml::appendText($document, $order, 'reference', $this->reference, Xml::PREFIX_GLOBAL);
		Xml::appendText($document, $order, 'costCenter', $this->costCenter, Xml::PREFIX_GLOBAL);

		foreach ($this->lines as $line)
		{
			$order->appendChild($line->toXml($document, Xml::PREFIX_GLOBAL));
		}

		foreach ($this->boxes as $box)
		{
			$order->appendChild($box->toXml($document, Xml::PREFIX_GLOBAL));
		}

		return $order;
	}

	public static function fromXml(SimpleXMLElement $xml): static
	{
		$order = new static((string)$xml->reference);

		if (isset($xml->costCenter) && trim((string)$xml->costCenter) !== '')
		{
			$order->costCenter((string)$xml->costCenter);
		}

		foreach ($xml->orderLine ?? [] as $line)
		{
			$order->lines[] = OrderLine::fromXml($line);
		}

		foreach ($xml->box ?? [] as $box)
		{
			$order->addBox(Box::fromXml($box));
		}

		return $order;
	}
}
