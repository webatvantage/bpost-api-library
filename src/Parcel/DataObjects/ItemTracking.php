<?php

namespace Webatvantage\Bpost\Api\Parcel\DataObjects;

use DateTimeImmutable;
use DateTimeInterface;
use Dom\Element;
use Webatvantage\Bpost\Api\Contracts\XmlDeserializable;
use Webatvantage\Bpost\Api\Parcel\Support\Xml;

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

	public static function fromXml(Element $xml): static
	{
		$states = [];

		foreach (Xml::children($xml, 'stateInfo') as $state)
		{
			$states[] = StateInfo::fromXml($state);
		}

		$sender = Xml::child($xml, 'sender');
		$addressee = Xml::child($xml, 'addressee');
		$itemDetail = Xml::child($xml, 'itemDetail');
		$pickupPoint = Xml::child($xml, 'pickupPoint');
		$deliveryTime = Xml::text($xml, 'deliveryTime');

		return new static(
			itemCode: Xml::text($xml, 'itemCode'),
			sender: $sender === null ? null : Sender::fromXml($sender),
			addressee: $addressee === null ? null : Addressee::fromXml($addressee),
			// bpost lower-cases the d in departure but not in destination.
			cityOrCountryOfDeparture: Xml::text($xml, 'cityOrCountryOfdeparture'),
			cityOrCountryOfDestination: Xml::text($xml, 'cityOrCountryOfDestination'),
			nameOfDestination: Xml::text($xml, 'nameOfDestination'),
			deliveryTime: $deliveryTime === null ? null : new DateTimeImmutable($deliveryTime),
			customerReference: Xml::text($xml, 'customerReference'),
			itemDetail: $itemDetail === null ? null : ItemDetail::fromXml($itemDetail),
			states: $states,
			trackingId: Xml::text($xml, 'trackingId'),
			pickupPoint: $pickupPoint === null ? null : PickupPoint::fromXml($pickupPoint),
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
