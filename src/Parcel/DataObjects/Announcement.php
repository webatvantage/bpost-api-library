<?php

namespace Webatvantage\Bpost\Api\Parcel\DataObjects;

use Webatvantage\Bpost\Api\Contracts\Option;
use Webatvantage\Bpost\Api\DataObjects\OpeningHours;
use Webatvantage\Bpost\Api\Parcel\Enums\DeliveryMethod;
use Webatvantage\Bpost\Api\Parcel\Enums\ParcelNamespace;
use Webatvantage\Bpost\Api\Support\Validate;
use Webatvantage\Bpost\Api\Support\XmlDocument;
use Webatvantage\Bpost\Api\Support\XmlElement;

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
		Validate::maxLength('itemCode', $itemCode, 30);
		Validate::between('weightInGrams', $weightInGrams, self::MIN_WEIGHT, self::MAX_WEIGHT);
	}

	/**
	 * The VAS code describing the services on the parcel. National shipments only.
	 */
	public function productCode(string $productCode): static
	{
		$this->productCode = Validate::maxLength('productCode', $productCode, 3);

		return $this;
	}

	public function type(string $type): static
	{
		$this->type = Validate::maxLength('type', $type, 2);

		return $this;
	}

	public function customerReference(string $customerReference): static
	{
		$this->customerReference = Validate::maxLength('customerReference', $customerReference, 50);

		return $this;
	}

	public function costCenter(string $costCenter): static
	{
		$this->costCenter = Validate::maxLength('costCenter', $costCenter, 50);

		return $this;
	}

	public function freeTextCustomerReference1(string $reference): static
	{
		$this->freeTextCustomerReference1 = Validate::maxLength('freeTextCustomerReference1', $reference, 50);

		return $this;
	}

	public function freeTextCustomerReference2(string $reference): static
	{
		$this->freeTextCustomerReference2 = Validate::maxLength('freeTextCustomerReference2', $reference, 50);

		return $this;
	}

	public function receiverOpeningHours(OpeningHours $openingHours): static
	{
		$this->receiverOpeningHours = $openingHours;

		return $this;
	}

	public function receiverDesiredDeliveryPlace(string $place): static
	{
		$this->receiverDesiredDeliveryPlace = Validate::maxLength('receiverDesiredDeliveryPlace', $place, 50);

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

	public function toXml(XmlDocument $document, string $accountId): XmlElement
	{
		$namespace = ParcelNamespace::Announcement;
		$announcement = $document->root('announcement', $namespace);
		ParcelNamespace::declareOn($announcement);

		$announcement->appendText('accountId', $accountId, $namespace);
		$announcement->appendText('type', $this->type, $namespace);
		$announcement->appendText('itemCode', $this->itemCode, $namespace);
		$announcement->appendText('productCode', $this->productCode, $namespace);

		$this->sender->toXml($announcement, $namespace);
		$this->receiver->toXml($announcement, $namespace);

		if ($this->receiverOpeningHours !== null && !$this->receiverOpeningHours->isEmpty())
		{
			$this->receiverOpeningHours->toXml($announcement, $namespace, 'receiverOpeningHours');
		}

		$announcement->appendText(
			'receiverDesiredDeliveryPlace',
			$this->receiverDesiredDeliveryPlace,
			$namespace,
		);
		$announcement->appendText('weightInGrams', $this->weightInGrams, $namespace);
		$announcement->appendText('customerReference', $this->customerReference, $namespace);
		$announcement->appendText('costCenter', $this->costCenter, $namespace);
		$announcement->appendText(
			'freeTextCustomerReference1',
			$this->freeTextCustomerReference1,
			$namespace,
		);
		$announcement->appendText(
			'freeTextCustomerReference2',
			$this->freeTextCustomerReference2,
			$namespace,
		);

		if ($this->international !== null)
		{
			$this->international->toXml($announcement, $namespace);
		}

		$method = $announcement->appendElement('deliveryMethod', $namespace);
		$method->appendElement($this->deliveryMethod->value, ParcelNamespace::Common);

		if (count($this->options) > 0)
		{
			$options = $announcement->appendElement('options', $namespace);

			foreach ($this->options as $option)
			{
				$option->toXml($options, ParcelNamespace::Common);
			}
		}

		if ($this->dimensions !== null)
		{
			$this->dimensions->toXml($announcement, $namespace);
		}

		return $announcement;
	}
}
