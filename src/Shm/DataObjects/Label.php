<?php

namespace Webatvantage\Bpost\Api\Shm\DataObjects;

use SimpleXMLElement;
use Webatvantage\Bpost\Api\Contracts\XmlDeserializable;

/**
 * One printable label.
 *
 * A label covers one box, but carries two barcodes when return labels were asked for. PDF and PNG
 * arrive base64-encoded in <bytes> and are decoded here; ZPL arrives as text in <zplCode>.
 */
class Label implements XmlDeserializable
{
	/**
	 * @param array<int, Barcode> $barcodes
	 */
	public function __construct(
		public private(set) array $barcodes = [],
		public private(set) ?string $mimeType = null,
		public private(set) ?string $bytes = null,
		public private(set) ?string $zplCode = null,
	) {}

	public static function fromXml(SimpleXMLElement $xml): static
	{
		$barcodes = [];

		foreach ($xml->barcodeWithReference ?? [] as $barcode)
		{
			$barcodes[] = Barcode::fromXml($barcode);
		}

		foreach ($xml->barcode ?? [] as $barcode)
		{
			$barcodes[] = Barcode::fromXml($barcode);
		}

		return new static(
			$barcodes,
			isset($xml->mimeType) ? trim((string)$xml->mimeType) : null,
			isset($xml->bytes) ? (string)base64_decode((string)$xml->bytes, true) : null,
			isset($xml->zplCode) ? (string)$xml->zplCode : null,
		);
	}

	/**
	 * The first barcode, which is the outbound one.
	 */
	public function barcode(): ?string
	{
		return $this->barcodes[0]->barcode ?? null;
	}

	/**
	 * What to write to a file: the decoded image, or the ZPL text.
	 */
	public function contents(): ?string
	{
		return $this->bytes ?? $this->zplCode;
	}
}
