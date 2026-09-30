<?php

namespace Webatvantage\Bpost\Api\Shm\DataObjects;

use Webatvantage\Bpost\Api\Contracts\XmlDeserializable;
use Webatvantage\Bpost\Api\Exceptions\InvalidArgumentException;
use Webatvantage\Bpost\Api\Exceptions\InvalidLengthException;
use Webatvantage\Bpost\Api\Exceptions\UnexpectedValueException;
use Webatvantage\Bpost\Api\Shm\Enums\ShmNamespace;
use Webatvantage\Bpost\Api\Support\Validate;
use Webatvantage\Bpost\Api\Support\XmlDocument;
use Webatvantage\Bpost\Api\Support\XmlElement;

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

	/**
	 * @throws InvalidLengthException
	 */
	public function __construct(public private(set) string $reference)
	{
		Validate::maxLength('reference', $reference, 50);
	}

	/**
	 * Groups barcodes on the invoice. bpost does not allow a unique value per barcode.
	 *
	 * @throws InvalidLengthException
	 */
	public function costCenter(string $costCenter): static
	{
		$this->costCenter = Validate::maxLength('costCenter', $costCenter, 50);

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
	 *
	 * @throws InvalidArgumentException
	 */
	public function toXml(XmlDocument $document, string $accountId): XmlElement
	{
		$order = $document->root('order', ShmNamespace::Global);
		ShmNamespace::declareOn($order);

		$order->appendText('accountId', $accountId, ShmNamespace::Global);
		$order->appendText('reference', $this->reference, ShmNamespace::Global);
		$order->appendText('costCenter', $this->costCenter, ShmNamespace::Global);

		foreach ($this->lines as $line)
		{
			$line->toXml($order, ShmNamespace::Global);
		}

		foreach ($this->boxes as $box)
		{
			$box->toXml($order, ShmNamespace::Global);
		}

		return $order;
	}

	/**
	 * @throws UnexpectedValueException
	 */
	public static function fromXml(XmlElement $xml): static
	{
		$order = Validate::reading(static fn (): static => new static($xml->text('reference') ?? ''));

		$order->costCenter = $xml->text('costCenter');

		foreach ($xml->children('orderLine') as $line)
		{
			$order->lines[] = OrderLine::fromXml($line);
		}

		foreach ($xml->children('box') as $box)
		{
			$order->addBox(Box::fromXml($box));
		}

		return $order;
	}
}
