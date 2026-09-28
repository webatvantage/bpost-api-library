<?php

namespace Webatvantage\Bpost\Api\Tests\Geo\Resources;

use Webatvantage\Bpost\Api\Enums\Language;
use Webatvantage\Bpost\Api\Geo\Enums\PointType;
use Webatvantage\Bpost\Api\Tests\Geo\GeoTestCase;

class ServicePointResourceTest extends GeoTestCase
{
	/**
	 * 3.x dropped Function, Partner and AppId from this URL during a refactor, leaving a link bpost
	 * could not answer. Its own connection test still asserted the correct shape.
	 */
	public function test_the_page_url_carries_the_function_and_credentials()
	{
		$url = $this->client()->servicePoints()->pageUrl('009800', PointType::PostOffice);

		$this->assertStringStartsWith('https://pudo.bpost.cloud/Locator?', $url);
		$this->assertStringContainsString('Function=page', $url);
		$this->assertStringContainsString('Partner=999999', $url);
		$this->assertStringContainsString('AppId=A001', $url);
		$this->assertStringContainsString('Id=009800', $url);
		$this->assertStringContainsString('Type=1', $url);
	}

	public function test_the_page_url_can_be_narrowed()
	{
		$url = $this->client()->servicePoints()
			->page('009800', PointType::ParcelLocker)
			->language(Language::FR)
			->withAttributes()
			->toUrl('https://pudo.bpost.cloud');

		$this->assertStringContainsString('Language=FR', $url);
		$this->assertStringContainsString('IncludeAttributes=1', $url);
	}

	public function test_building_a_page_url_sends_nothing()
	{
		$this->client()->servicePoints()->pageUrl('009800', PointType::PostOffice);

		$this->assertCount(0, $this->recorded);
	}
}
