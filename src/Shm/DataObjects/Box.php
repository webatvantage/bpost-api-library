<?php

namespace Webatvantage\Bpost\Api\Shm\DataObjects;

use Dom\Element;
use Dom\XMLDocument;
use Webatvantage\Bpost\Api\Contracts\XmlDeserializable;
use Webatvantage\Bpost\Api\Contracts\XmlSerializable;
use Webatvantage\Bpost\Api\Shm\DataObjects\Box\DeliveryBox;
use Webatvantage\Bpost\Api\Shm\DataObjects\Box\DeliveryBoxFactory;
use Webatvantage\Bpost\Api\Shm\Enums\BoxStatus;
use Webatvantage\Bpost\Api\Shm\Support\Xml;
use Webatvantage\Bpost\Api\Support\Assert;

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

	public function toXml(XMLDocument $document, ?string $prefix = Xml::PREFIX_GLOBAL): Element
	{
		$box = Xml::element($document, 'box', $prefix);

		if ($this->sender !== null)
		{
			$box->append($this->sender->toXml($document, $prefix));
		}

		if ($this->deliveryBox !== null)
		{
			$box->append($this->deliveryBox->toXml($document, $prefix));
		}

		Xml::appendText($document, $box, 'remark', $this->remark, $prefix);

		Xml::appendText($document, $box, 'additionalCustomerReference', $this->additionalCustomerReference, $prefix);
		Xml::appendText($document, $box, 'barcode', $this->barcode, $prefix);

		return $box;
	}

	public static function fromXml(Element $xml): static
	{
		$box = new static();

		$sender = Xml::child($xml, 'sender');

		if ($sender !== null)
		{
			$box->sender(Sender::fromXml($sender));
		}

		// Either wrapper holds one delivery method; which namespace it claims does not matter.
		foreach (['nationalBox', 'internationalBox'] as $wrapper)
		{
			$element = Xml::child($xml, $wrapper);

			if ($element === null)
			{
				continue;
			}

			foreach ($element->children as $method)
			{
				$box->deliverTo(DeliveryBoxFactory::fromXml($method));
			}
		}

		$remark = Xml::text($xml, 'remark');

		if ($remark !== null)
		{
			$box->remark($remark);
		}

		$additionalCustomerReference = Xml::text($xml, 'additionalCustomerReference');

		if ($additionalCustomerReference !== null)
		{
			$box->additionalCustomerReference($additionalCustomerReference);
		}

		$barcode = Xml::text($xml, 'barcode');

		if ($barcode !== null)
		{
			$box->barcode = strtoupper($barcode);
		}

		$status = Xml::text($xml, 'status');

		if ($status !== null)
		{
			$box->status = BoxStatus::tryFrom(strtoupper($status));
		}

		return $box;
	}
}
