<?php

namespace Webatvantage\Bpost\Api\Shm\DataObjects\Box;

use Webatvantage\Bpost\Api\Contracts\XmlNamespace;
use Webatvantage\Bpost\Api\Shm\Enums\ShmNamespace;

/**
 * A box delivered within Belgium.
 *
 * National elements sit in the default namespace, so they carry no prefix.
 */
abstract class NationalBox extends DeliveryBox
{
	protected function wrapperName(): string
	{
		return 'nationalBox';
	}

	protected function childNamespace(): XmlNamespace
	{
		return ShmNamespace::National;
	}
}
