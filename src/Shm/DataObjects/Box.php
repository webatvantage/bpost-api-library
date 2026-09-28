<?php

namespace Webatvantage\Bpost\Api\Shm\DataObjects;

use DOMDocument;
use DOMElement;
use SimpleXMLElement;
use Webatvantage\Bpost\Api\Contracts\XmlDeserializable;
use Webatvantage\Bpost\Api\Contracts\XmlSerializable;
use Webatvantage\Bpost\Api\Shm\DataObjects\Box\DeliveryBox;
use Webatvantage\Bpost\Api\Shm\DataObjects\Box\DeliveryBoxFactory;
use Webatvantage\Bpost\Api\Shm\Enums\BoxStatus;
use Webatvantage\Bpost\Api\Shm\Support\Assert;
use Webatvantage\Bpost\Api\Shm\Support\Xml;

/**
 * One parcel in an order.
 *
 * An order can hold several, each with its own delivery method, so a basket split across two
 * parcels is one order with two boxes.
 */
class Box implements XmlDeserializable, XmlSerializable
{
	public private(set) ?Sender $sender = null;

	public private(set) ?DeliveryBox $deliveryBox = null;

	public private(set) ?string $remark = null;

	public private(set) ?string $additionalCustomerReference = null;

	public private(set) ?string $barcode = null;

	public private(set) ?BoxStatus $status = null;

	public static function make(): static
	{
		return new static();
	}

	public function sender(Sender $sender): static
	{
		$this->sender = $sender;

		return $this;
	}

	/**
	 * Where this parcel goes. The delivery method decides whether it is a national or an
	 * international box.
	 */
	public function deliverTo(DeliveryBox $deliveryBox): static
	{
		$this->deliveryBox = $deliveryBox;

		return $this;
	}

	/**
	 * Free text, printed on the label below the delivery address.
	 */
	public function remark(string $remark): static
	{
		$this->remark = Assert::maxLength('remark', $remark, 50);

		return $this;
	}

	/**
	 * Free text for cross-referencing, not printed.
	 */
	public function additionalCustomerReference(string $reference): static
	{
		$this->additionalCustomerReference = Assert::maxLength('additionalCustomerReference', $reference, 50);

		return $this;
	}

	public function toXml(DOMDocument $document, ?string $prefix = Xml::PREFIX_GLOBAL): DOMElement
	{
		$box = $document->createElement(Xml::prefixed('box', $prefix));

		if ($this->sender !== null)
		{
			$box->appendChild($this->sender->toXml($document, $prefix));
		}

		if ($this->deliveryBox !== null)
		{
			$box->appendChild($this->deliveryBox->toXml($document, $prefix));
		}

		Xml::appendText($document, $box, 'remark', $this->remark, $prefix);

		// 3.x appended a "+PHP8.2" suffix and wrote the element even when there was no reference,
		// so every box carried one whether the caller set it or not.
		Xml::appendText($document, $box, 'additionalCustomerReference', $this->additionalCustomerReference, $prefix);
		Xml::appendText($document, $box, 'barcode', $this->barcode, $prefix);

		return $box;
	}

	public static function fromXml(SimpleXMLElement $xml): static
	{
		$box = new static();

		if (isset($xml->sender))
		{
			$box->sender(Sender::fromXml(Xml::readChildren($xml->sender, Xml::READ_COMMON)));
		}

		foreach (['nationalBox' => Xml::READ_NATIONAL, 'internationalBox' => Xml::READ_INTERNATIONAL] as $wrapper => $namespace)
		{
			if (!isset($xml->{$wrapper}))
			{
				continue;
			}

			foreach (Xml::readChildren($xml->{$wrapper}, $namespace) as $method)
			{
				$box->deliverTo(DeliveryBoxFactory::fromXml($method));
			}
		}

		if (isset($xml->remark) && trim((string)$xml->remark) !== '')
		{
			$box->remark((string)$xml->remark);
		}

		if (isset($xml->additionalCustomerReference) && trim((string)$xml->additionalCustomerReference) !== '')
		{
			$box->additionalCustomerReference((string)$xml->additionalCustomerReference);
		}

		if (isset($xml->barcode) && trim((string)$xml->barcode) !== '')
		{
			$box->barcode = strtoupper(trim((string)$xml->barcode));
		}

		if (isset($xml->status) && trim((string)$xml->status) !== '')
		{
			$box->status = BoxStatus::tryFrom(strtoupper(trim((string)$xml->status)));
		}

		return $box;
	}
}
