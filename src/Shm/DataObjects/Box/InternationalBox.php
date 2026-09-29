<?php

namespace Webatvantage\Bpost\Api\Shm\DataObjects\Box;

use Webatvantage\Bpost\Api\Contracts\XmlNamespace;
use Webatvantage\Bpost\Api\Exceptions\InvalidArgumentException;
use Webatvantage\Bpost\Api\Exceptions\InvalidLengthException;
use Webatvantage\Bpost\Api\Exceptions\InvalidValueException;
use Webatvantage\Bpost\Api\Exceptions\UnexpectedValueException;
use Webatvantage\Bpost\Api\Shm\DataObjects\Customs\CustomsInfo;
use Webatvantage\Bpost\Api\Shm\DataObjects\Receiver;
use Webatvantage\Bpost\Api\Shm\Enums\ShmNamespace;
use Webatvantage\Bpost\Api\Support\XmlElement;

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

	protected function childNamespace(): XmlNamespace
	{
		return ShmNamespace::International;
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
	 *
	 * @throws InvalidArgumentException
	 */
	protected function appendShared(XmlElement $element): void
	{
		$namespace = $this->childNamespace();

		$element->appendText('product', $this->product?->value, $namespace);

		$this->appendOptions($element);

		if ($this->receiver !== null)
		{
			$this->receiver->toXml($element, $namespace);
		}

		$element->appendText('parcelWeight', $this->weight, $namespace);

		if ($this->customsInfo !== null)
		{
			$this->customsInfo->toXml($element);
		}
	}

	/**
	 * @throws InvalidLengthException
	 * @throws InvalidValueException
	 * @throws UnexpectedValueException
	 */
	protected function readShared(XmlElement $xml): void
	{
		$this->readCommon($xml, 'parcelWeight');

		$receiver = $xml->child('receiver');

		if ($receiver !== null)
		{
			$this->receiver(Receiver::fromXml($receiver));
		}

		$customsInfo = $xml->child('customsInfo');

		if ($customsInfo !== null)
		{
			$this->customsInfo(CustomsInfo::fromXml($customsInfo));
		}
	}
}
