<?php

namespace Webatvantage\Bpost\Api\Parcel\DataObjects;

use DateTimeImmutable;
use DateTimeInterface;
use SimpleXMLElement;
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

	public static function fromXml(SimpleXMLElement $xml): static
	{
		$value = static fn (string $name): ?string => isset($xml->{$name}) && trim((string)$xml->{$name}) !== ''
			? trim((string)$xml->{$name})
			: null;

		$states = [];

		foreach ($xml->stateInfo ?? [] as $state)
		{
			$states[] = StateInfo::fromXml($state);
		}

		$deliveryTime = $value('deliveryTime');

		return new static(
			itemCode: $value('itemCode'),
			// The parties are in the announcement service's common namespace, not tracking's own.
			sender: isset($xml->sender)
				? Sender::fromXml(Xml::readChildren($xml->sender, Xml::COMMON))
				: null,
			addressee: isset($xml->addressee)
				? Addressee::fromXml(Xml::readChildren($xml->addressee, Xml::COMMON))
				: null,
			// bpost lower-cases the d in departure but not in destination.
			cityOrCountryOfDeparture: $value('cityOrCountryOfdeparture'),
			cityOrCountryOfDestination: $value('cityOrCountryOfDestination'),
			nameOfDestination: $value('nameOfDestination'),
			deliveryTime: $deliveryTime === null ? null : new DateTimeImmutable($deliveryTime),
			customerReference: $value('customerReference'),
			itemDetail: isset($xml->itemDetail) ? ItemDetail::fromXml($xml->itemDetail) : null,
			states: $states,
			trackingId: $value('trackingId'),
			pickupPoint: isset($xml->pickupPoint) ? PickupPoint::fromXml($xml->pickupPoint) : null,
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
