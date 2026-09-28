<?php

namespace Webatvantage\Bpost\Api\Shm\Enums;

use Webatvantage\Bpost\Api\Exceptions\InvalidValueException;

/**
 * The image formats a label can come back in.
 *
 * Each maps to its own Accept media type, and bpost versions them separately — the PDF and PNG
 * types sit at v3.4 while ZPL is at v5.
 */
enum LabelOutput: string
{
	case Pdf = 'pdf';
	case Png = 'image';
	case Zpl = 'zpl';

	public function acceptHeader(): string
	{
		return match ($this)
		{
			self::Pdf => 'application/vnd.bpost.shm-label-pdf-v3.4+XML',
			self::Png => 'application/vnd.bpost.shm-label-image-v3.4+XML',
			self::Zpl => 'application/vnd.bpost.shm-label-zpl-v5+XML',
		};
	}

	/**
	 * ZPL is only produced for A6, and only for 203 dpi printers.
	 *
	 * @throws InvalidValueException
	 */
	public function assertSupports(LabelFormat $format): void
	{
		if ($this === self::Zpl && $format !== LabelFormat::A6)
		{
			throw new InvalidValueException('labelFormat', $format->value, [LabelFormat::A6->value]);
		}
	}
}
