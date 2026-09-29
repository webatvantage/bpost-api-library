<?php

namespace Webatvantage\Bpost\Api\Shm\DataObjects;

use Dom\Element;
use Webatvantage\Bpost\Api\Contracts\XmlDeserializable;
use Webatvantage\Bpost\Api\Shm\Support\Xml;

/**
 * A parcel barcode, and the order reference it belongs to.
 *
 * A return label's barcode ends in 050 where the outbound one ends in 030, which is how the two
 * are told apart when a label is requested with return labels.
 */
class Barcode implements XmlDeserializable
{
	public function __construct(public private(set) string $barcode, public private(set) ?string $reference = null) {}

	public static function fromXml(Element $xml): static
	{
		// The v3.4 media types wrap the barcode with its reference; the older ones give it bare.
		$barcode = Xml::child($xml, 'barcode');

		if ($barcode !== null)
		{
			return new static(
				trim($barcode->textContent),
				Xml::text($xml, 'reference'),
			);
		}

		return new static(trim($xml->textContent));
	}

	public function isReturnLabel(): bool
	{
		return str_ends_with($this->barcode, '050');
	}
}
