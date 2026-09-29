<?php

namespace Webatvantage\Bpost\Api\Shm\DataObjects;

use Webatvantage\Bpost\Api\Contracts\XmlDeserializable;
use Webatvantage\Bpost\Api\Contracts\XmlNamespace;
use Webatvantage\Bpost\Api\Contracts\XmlSerializable;
use Webatvantage\Bpost\Api\Shm\DataObjects\Box\DeliveryBox;
use Webatvantage\Bpost\Api\Shm\DataObjects\Box\DeliveryBoxFactory;
use Webatvantage\Bpost\Api\Shm\Enums\BoxStatus;
use Webatvantage\Bpost\Api\Shm\Enums\ShmNamespace;
use Webatvantage\Bpost\Api\Support\Validate;
use Webatvantage\Bpost\Api\Support\XmlElement;

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
		$this->remark = Validate::maxLength('remark', $remark, 50);

		return $this;
	}

	/**
	 * Free text for cross-referencing, not printed.
	 */
	public function additionalCustomerReference(string $reference): static
	{
		$this->additionalCustomerReference = Validate::maxLength('additionalCustomerReference', $reference, 50);

		return $this;
	}

	public function toXml(XmlElement $parent, ?XmlNamespace $namespace = ShmNamespace::Global): XmlElement
	{
		$box = $parent->appendElement('box', $namespace);

		if ($this->sender !== null)
		{
			$this->sender->toXml($box, $namespace);
		}

		if ($this->deliveryBox !== null)
		{
			$this->deliveryBox->toXml($box, $namespace);
		}

		$box->appendText('remark', $this->remark, $namespace);

		$box->appendText('additionalCustomerReference', $this->additionalCustomerReference, $namespace);
		$box->appendText('barcode', $this->barcode, $namespace);

		return $box;
	}

	public static function fromXml(XmlElement $xml): static
	{
		$box = new static();

		$sender = $xml->child('sender');

		if ($sender !== null)
		{
			$box->sender(Sender::fromXml($sender));
		}

		// Either wrapper holds one delivery method; which namespace it claims does not matter.
		foreach (['nationalBox', 'internationalBox'] as $wrapper)
		{
			$element = $xml->child($wrapper);

			if ($element === null)
			{
				continue;
			}

			foreach ($element->childElements() as $method)
			{
				$box->deliverTo(DeliveryBoxFactory::fromXml($method));
			}
		}

		$remark = $xml->text('remark');

		if ($remark !== null)
		{
			$box->remark($remark);
		}

		$additionalCustomerReference = $xml->text('additionalCustomerReference');

		if ($additionalCustomerReference !== null)
		{
			$box->additionalCustomerReference($additionalCustomerReference);
		}

		$barcode = $xml->text('barcode');

		if ($barcode !== null)
		{
			$box->barcode = strtoupper($barcode);
		}

		$status = $xml->text('status');

		if ($status !== null)
		{
			$box->status = BoxStatus::tryFrom(strtoupper($status));
		}

		return $box;
	}
}
