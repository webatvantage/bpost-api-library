<?php

namespace Webatvantage\Bpost\Api\Shm\DataObjects;

use SimpleXMLElement;
use Webatvantage\Bpost\Api\Contracts\XmlDeserializable;

/**
 * A parcel barcode, and the order reference it belongs to.
 *
 * A return label's barcode ends in 050 where the outbound one ends in 030, which is how the two
 * are told apart when a label is requested with return labels.
 */
class Barcode implements XmlDeserializable
{
	public function __construct(public private(set) string $barcode, public private(set) ?string $reference = null) {}

	public static function fromXml(SimpleXMLElement $xml): static
	{
		// The v3.4 media types wrap the barcode with its reference; the older ones give it bare.
		if (isset($xml->barcode))
		{
			return new static(
				trim((string)$xml->barcode),
				isset($xml->reference) ? trim((string)$xml->reference) : null,
			);
		}

		return new static(trim((string)$xml));
	}

	public function isReturnLabel(): bool
	{
		return str_ends_with($this->barcode, '050');
	}
}
