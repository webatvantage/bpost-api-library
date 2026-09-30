<?php

namespace Webatvantage\Bpost\Api\Geo\Traits;

use Webatvantage\Bpost\Api\Contracts\Request;
use Webatvantage\Bpost\Api\Enums\Language;
use Webatvantage\Bpost\Api\Exceptions\InvalidValueException;

/**
 * The Language parameter, which every Geolocator operation spells the same way.
 *
 * @mixin Request
 */
trait HasLanguageParameter
{
	/**
	 * @throws InvalidValueException
	 */
	public function language(Language $language): static
	{
		return $this->addParameter('Language', static::assertAvailable($language));
	}

	/**
	 * @throws InvalidValueException
	 */
	protected static function assertAvailable(Language $language): Language
	{
		if (!in_array($language, [Language::NL, Language::FR], true))
		{
			throw new InvalidValueException('Language', $language->value, [
				Language::NL->value,
				Language::FR->value,
			]);
		}

		return $language;
	}
}
