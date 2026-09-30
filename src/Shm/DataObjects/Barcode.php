<?php

namespace Webatvantage\Bpost\Api\Shm\DataObjects;

use Webatvantage\Bpost\Api\Contracts\XmlDeserializable;
use Webatvantage\Bpost\Api\Support\XmlElement;

/**
 * A parcel barcode, and the order reference it belongs to.
 *
 * A return label's barcode ends in 050 where the outbound one ends in 030, which is how the two
 * are told apart when a label is requested with return labels.
 */
class Barcode implements XmlDeserializable
{
	public function __construct(public private(set) string $barcode, public private(set) ?string $reference = null) {}

	public static function fromXml(XmlElement $xml): static
	{
		// The v3.4 media types wrap the barcode with its reference; the older ones give it bare.
		$barcode = $xml->child('barcode');

		if ($barcode !== null)
		{
			return new static(
				barcode: $barcode->ownText() ?? '',
				reference: $xml->text('reference'),
			);
		}

		return new static($xml->ownText() ?? '');
	}

	public function isReturnLabel(): bool
	{
		return str_ends_with($this->barcode, '050');
	}
}
