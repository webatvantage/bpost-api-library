<?php

namespace Webatvantage\Bpost\Api\Shm\DataObjects\Box;

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

	protected function childPrefix(): ?string
	{
		return null;
	}
}
