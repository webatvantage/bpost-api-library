<?php

namespace Webatvantage\Bpost\Api\Shm\DataObjects\Box;

use DOMDocument;
use DOMElement;
use SimpleXMLElement;
use Webatvantage\Bpost\Api\Shm\DataObjects\Customs\CustomsInfo;
use Webatvantage\Bpost\Api\Shm\DataObjects\Receiver;
use Webatvantage\Bpost\Api\Shm\Support\Xml;

/**
 * A box leaving Belgium.
 *
 * International elements carry their own prefix, and the weight element is parcelWeight rather
 * than weight.
 */
abstract class InternationalBox extends DeliveryBox
{
	public private(set) ?Receiver $receiver = null;

	public private(set) ?CustomsInfo $customsInfo = null;

	protected function wrapperName(): string
	{
		return 'internationalBox';
	}

	protected function childPrefix(): ?string
	{
		return Xml::PREFIX_INTERNATIONAL;
	}

	public function receiver(Receiver $receiver): static
	{
		$this->receiver = $receiver;

		return $this;
	}

	/**
	 * Not used for bpack Europe Business: nothing is declared inside the EU customs zone.
	 */
	public function customsInfo(CustomsInfo $customsInfo): static
	{
		$this->customsInfo = $customsInfo;

		return $this;
	}

	/**
	 * Write product, options, receiver and parcelWeight, which both international methods share
	 * and in this order.
	 */
	protected function appendShared(DOMDocument $document, DOMElement $element): void
	{
		$prefix = $this->childPrefix();

		Xml::appendText($document, $element, 'product', $this->product?->value, $prefix);

		$options = $this->buildOptions($document);

		if ($options !== null)
		{
			$element->appendChild($options);
		}

		if ($this->receiver !== null)
		{
			$element->appendChild($this->receiver->toXml($document, $prefix));
		}

		Xml::appendText($document, $element, 'parcelWeight', $this->weight, $prefix);

		if ($this->customsInfo !== null)
		{
			$element->appendChild($this->customsInfo->toXml($document));
		}
	}

	protected function readShared(SimpleXMLElement $xml): void
	{
		$this->readCommon($xml, 'parcelWeight');

		if (isset($xml->receiver))
		{
			$this->receiver(Receiver::fromXml(Xml::readChildren($xml->receiver, Xml::READ_COMMON)));
		}

		if (isset($xml->customsInfo))
		{
			$this->customsInfo(CustomsInfo::fromXml($xml->customsInfo));
		}
	}
}
