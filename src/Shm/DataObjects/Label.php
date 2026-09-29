<?php

namespace Webatvantage\Bpost\Api\Shm\DataObjects;

use Webatvantage\Bpost\Api\Contracts\XmlDeserializable;
use Webatvantage\Bpost\Api\Exceptions\UnserializableResponseException;
use Webatvantage\Bpost\Api\Support\XmlElement;

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

	public static function fromXml(XmlElement $xml): static
	{
		$barcodes = [];

		foreach ($xml->children('barcodeWithReference') as $barcode)
		{
			$barcodes[] = Barcode::fromXml($barcode);
		}

		foreach ($xml->children('barcode') as $barcode)
		{
			$barcodes[] = Barcode::fromXml($barcode);
		}

		return new static(
			$barcodes,
			$xml->text('mimeType'),
			self::decode($xml),
			$xml->text('zplCode'),
		);
	}

	/**
	 * The decoded <bytes>, or null when there are none.
	 *
	 * @throws UnserializableResponseException
	 */
	private static function decode(XmlElement $xml): ?string
	{
		$bytes = $xml->text('bytes');

		if ($bytes === null)
		{
			return null;
		}

		$decoded = base64_decode($bytes, true);

		if ($decoded === false)
		{
			throw new UnserializableResponseException(
				message: 'The label bytes bpost returned are not valid base64.',
				statusCode: 200,
				body: $bytes,
			);
		}

		return $decoded;
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
