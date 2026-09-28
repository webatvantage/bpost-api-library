<?php

namespace Webatvantage\Bpost\Api\Parcel\DataObjects;

use DOMDocument;
use DOMElement;
use Webatvantage\Bpost\Api\Contracts\OpeningHours;
use Webatvantage\Bpost\Api\Contracts\Option;
use Webatvantage\Bpost\Api\Parcel\Enums\DeliveryMethod;
use Webatvantage\Bpost\Api\Parcel\Support\Xml;
use Webatvantage\Bpost\Api\Support\Assert;

/**
 * Tells bpost a parcel is coming, before it reaches them.
 *
 * This is the alternative to the Shipping Manager for anyone printing their own labels: the
 * barcode already exists, and this supplies the data that would otherwise have come with the
 * order. It has to arrive before the parcel physically does, or bpost cannot act on the messaging
 * and value-added services it names.
 */
class Announcement
{
	/** bpost converts a weight of 0 to one kilo, so it is worth being explicit. */
	public const int MIN_WEIGHT = 100;

	public const int MAX_WEIGHT = 30_000;

	public private(set) string $type = '00';

	public private(set) ?string $productCode = null;

	public private(set) ?OpeningHours $receiverOpeningHours = null;

	public private(set) ?string $receiverDesiredDeliveryPlace = null;

	public private(set) ?string $customerReference = null;

	public private(set) ?string $costCenter = null;

	public private(set) ?string $freeTextCustomerReference1 = null;

	public private(set) ?string $freeTextCustomerReference2 = null;

	public private(set) ?InternationalInfo $international = null;

	public private(set) ?Dimensions $dimensions = null;

	/** @var array<int, Option> */
	public private(set) array $options = [];

	/**
	 * @param string $itemCode The barcode already printed on the parcel
	 * @param int $weightInGrams 100 to 30000
	 */
	public function __construct(
		public private(set) string $itemCode,
		public private(set) Sender $sender,
		public private(set) Receiver $receiver,
		public private(set) int $weightInGrams,
		public private(set) DeliveryMethod $deliveryMethod = DeliveryMethod::AtHome,
	) {
		Assert::maxLength('itemCode', $itemCode, 30);
		Assert::between('weightInGrams', $weightInGrams, self::MIN_WEIGHT, self::MAX_WEIGHT);
	}

	/**
	 * The VAS code describing the services on the parcel. National shipments only.
	 */
	public function productCode(string $productCode): static
	{
		$this->productCode = Assert::maxLength('productCode', $productCode, 3);

		return $this;
	}

	public function type(string $type): static
	{
		$this->type = Assert::maxLength('type', $type, 2);

		return $this;
	}

	public function customerReference(string $customerReference): static
	{
		$this->customerReference = Assert::maxLength('customerReference', $customerReference, 50);

		return $this;
	}

	public function costCenter(string $costCenter): static
	{
		$this->costCenter = Assert::maxLength('costCenter', $costCenter, 50);

		return $this;
	}

	public function freeTextCustomerReference1(string $reference): static
	{
		$this->freeTextCustomerReference1 = Assert::maxLength('freeTextCustomerReference1', $reference, 50);

		return $this;
	}

	public function freeTextCustomerReference2(string $reference): static
	{
		$this->freeTextCustomerReference2 = Assert::maxLength('freeTextCustomerReference2', $reference, 50);

		return $this;
	}

	public function receiverOpeningHours(OpeningHours $openingHours): static
	{
		$this->receiverOpeningHours = $openingHours;

		return $this;
	}

	public function receiverDesiredDeliveryPlace(string $place): static
	{
		$this->receiverDesiredDeliveryPlace = Assert::maxLength('receiverDesiredDeliveryPlace', $place, 50);

		return $this;
	}

	/**
	 * The customs declaration. Required for anything leaving Belgium.
	 */
	public function international(InternationalInfo $international): static
	{
		$this->international = $international;

		return $this;
	}

	/**
	 * Mandatory for bpack XL.
	 */
	public function dimensions(Dimensions $dimensions): static
	{
		$this->dimensions = $dimensions;

		return $this;
	}

	public function withOption(Option $option): static
	{
		$this->options[] = $option;

		return $this;
	}

	public function withOptions(Option ...$options): static
	{
		foreach ($options as $option)
		{
			$this->withOption($option);
		}

		return $this;
	}

	public function toXml(DOMDocument $document, string $accountId): DOMElement
	{
		$prefix = Xml::PREFIX_ANNOUNCEMENT;
		$announcement = $document->createElement(Xml::prefixed('announcement', $prefix));
		Xml::declareNamespaces($announcement);

		Xml::appendText($document, $announcement, 'accountId', $accountId, $prefix);
		Xml::appendText($document, $announcement, 'type', $this->type, $prefix);
		Xml::appendText($document, $announcement, 'itemCode', $this->itemCode, $prefix);
		Xml::appendText($document, $announcement, 'productCode', $this->productCode, $prefix);

		$announcement->appendChild($this->sender->toXml($document, $prefix));
		$announcement->appendChild($this->receiver->toXml($document, $prefix));

		if ($this->receiverOpeningHours !== null && !$this->receiverOpeningHours->isEmpty())
		{
			$announcement->appendChild(
				$this->receiverOpeningHours->toXml($document, $prefix, 'receiverOpeningHours'),
			);
		}

		Xml::appendText(
			$document,
			$announcement,
			'receiverDesiredDeliveryPlace',
			$this->receiverDesiredDeliveryPlace,
			$prefix,
		);
		Xml::appendText($document, $announcement, 'weightInGrams', $this->weightInGrams, $prefix);
		Xml::appendText($document, $announcement, 'customerReference', $this->customerReference, $prefix);
		Xml::appendText($document, $announcement, 'costCenter', $this->costCenter, $prefix);
		Xml::appendText(
			$document,
			$announcement,
			'freeTextCustomerReference1',
			$this->freeTextCustomerReference1,
			$prefix,
		);
		Xml::appendText(
			$document,
			$announcement,
			'freeTextCustomerReference2',
			$this->freeTextCustomerReference2,
			$prefix,
		);

		if ($this->international !== null)
		{
			$announcement->appendChild($this->international->toXml($document, $prefix));
		}

		$method = $document->createElement(Xml::prefixed('deliveryMethod', $prefix));
		$method->appendChild(
			$document->createElement(Xml::prefixed($this->deliveryMethod->value, Xml::PREFIX_COMMON)),
		);
		$announcement->appendChild($method);

		if (count($this->options) > 0)
		{
			$options = $document->createElement(Xml::prefixed('options', $prefix));

			foreach ($this->options as $option)
			{
				$options->appendChild($option->toXml($document));
			}

			$announcement->appendChild($options);
		}

		if ($this->dimensions !== null)
		{
			$announcement->appendChild($this->dimensions->toXml($document, $prefix));
		}

		return $announcement;
	}
}
