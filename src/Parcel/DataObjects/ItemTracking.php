<?php

namespace Webatvantage\Bpost\Api\Parcel\DataObjects;

use DateTimeInterface;
use Webatvantage\Bpost\Api\Contracts\XmlDeserializable;
use Webatvantage\Bpost\Api\Exceptions\UnexpectedValueException;
use Webatvantage\Bpost\Api\Support\XmlElement;

/**
 * Where a parcel has been, and where it is now.
 *
 * The scans arrive oldest first; latestState() is the one worth showing a customer.
 */
class ItemTracking implements XmlDeserializable
{
	/**
	 * @param array<int, StateInfo> $states
	 */
	public function __construct(
		public private(set) ?string $itemCode = null,
		public private(set) ?Sender $sender = null,
		public private(set) ?Addressee $addressee = null,
		public private(set) ?string $cityOrCountryOfDeparture = null,
		public private(set) ?string $cityOrCountryOfDestination = null,
		public private(set) ?string $nameOfDestination = null,
		public private(set) ?DateTimeInterface $deliveryTime = null,
		public private(set) ?string $customerReference = null,
		public private(set) ?ItemDetail $itemDetail = null,
		public private(set) array $states = [],
		public private(set) ?string $trackingId = null,
		public private(set) ?PickupPoint $pickupPoint = null,
	) {}

	/**
	 * @throws UnexpectedValueException
	 */
	public static function fromXml(XmlElement $xml): static
	{
		$states = [];

		foreach ($xml->children('stateInfo') as $state)
		{
			$states[] = StateInfo::fromXml($state);
		}

		$sender = $xml->child('sender');
		$addressee = $xml->child('addressee');
		$itemDetail = $xml->child('itemDetail');
		$pickupPoint = $xml->child('pickupPoint');

		return new static(
			itemCode: $xml->text('itemCode'),
			sender: isset($sender) ? Sender::fromXml($sender) : null,
			addressee: isset($addressee) ? Addressee::fromXml($addressee) : null,
			// bpost lower-cases the d in departure but not in destination.
			cityOrCountryOfDeparture: $xml->text('cityOrCountryOfdeparture'),
			cityOrCountryOfDestination: $xml->text('cityOrCountryOfDestination'),
			nameOfDestination: $xml->text('nameOfDestination'),
			deliveryTime: $xml->dateTime('deliveryTime'),
			customerReference: $xml->text('customerReference'),
			itemDetail: isset($itemDetail) ? ItemDetail::fromXml($itemDetail) : null,
			states: $states,
			trackingId: $xml->text('trackingId'),
			pickupPoint: isset($pickupPoint) ? PickupPoint::fromXml($pickupPoint) : null,
		);
	}

	/**
	 * The most recent scan, which is the parcel's current state.
	 */
	public function latestState(): ?StateInfo
	{
		return count($this->states) === 0 ? null : $this->states[count($this->states) - 1];
	}

	public function isDelivered(): bool
	{
		return $this->deliveryTime !== null;
	}

	/**
	 * The page bpost shows a customer for this parcel.
	 */
	public function trackingUrl(): ?string
	{
		return $this->trackingId === null ? null : 'https://track.bpost.be/id/' . $this->trackingId;
	}
}
