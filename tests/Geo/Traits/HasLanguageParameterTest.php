<?php

namespace Webatvantage\Bpost\Api\Tests\Geo\Traits;

use PHPUnit\Framework\Attributes\DataProvider;
use Webatvantage\Bpost\Api\Enums\Language;
use Webatvantage\Bpost\Api\Exceptions\InvalidValueException;
use Webatvantage\Bpost\Api\Geo\Enums\PointType;
use Webatvantage\Bpost\Api\Tests\Geo\GeoTestCase;

class HasLanguageParameterTest extends GeoTestCase
{
	/**
	 * @return array<string, array{Language}>
	 */
	public static function availableLanguages(): array
	{
		return ['NL' => [Language::NL], 'FR' => [Language::FR]];
	}

	/**
	 * @return array<string, array{Language}>
	 */
	public static function unavailableLanguages(): array
	{
		return ['EN' => [Language::EN], 'DE' => [Language::DE]];
	}

	#[DataProvider('availableLanguages')]
	public function test_a_search_takes_the_two_languages_the_geolocator_documents(Language $language)
	{
		$this->mockResponse(200, '<Poi/>');

		$this->client()->servicePoints()->nearest(zone: '1000')->language($language)->get();

		$this->assertSame($language->value, $this->sentQuery()['Language']);
	}

	/**
	 * The Shipping Manager's messaging takes all four, so Language carries EN and DE as well.
	 * Sending either to the Geolocator gets them quietly ignored, so they are refused here.
	 */
	#[DataProvider('unavailableLanguages')]
	public function test_a_search_refuses_a_language_the_geolocator_does_not_offer(Language $language)
	{
		$this->expectException(InvalidValueException::class);
		$this->expectExceptionMessage('possible values are: NL, FR');

		$this->client()->servicePoints()->nearest(zone: '1000')->language($language);
	}

	#[DataProvider('unavailableLanguages')]
	public function test_a_details_lookup_refuses_one_too(Language $language)
	{
		$this->expectException(InvalidValueException::class);

		$this->client()->servicePoints()->details('009800', PointType::PostOffice)->language($language);
	}

	#[DataProvider('unavailableLanguages')]
	public function test_an_all_points_download_refuses_one_too(Language $language)
	{
		$this->expectException(InvalidValueException::class);

		$this->client()->servicePoints()->all()->language($language);
	}

	/**
	 * The page URL is never sent by the library, but it is handed to a browser, so the same rule
	 * applies to it.
	 */
	#[DataProvider('unavailableLanguages')]
	public function test_a_page_url_refuses_one_too(Language $language)
	{
		$this->expectException(InvalidValueException::class);

		$this->client()->servicePoints()->page('009800', PointType::PostOffice)->language($language);
	}
}
