<?php

namespace Webatvantage\Bpost\Api\Geo\Requests;

use Webatvantage\Bpost\Api\ApiAdapter\HttpApiAdapter;
use Webatvantage\Bpost\Api\Enums\Language;
use Webatvantage\Bpost\Api\Exceptions\UnexpectedValueException;
use Webatvantage\Bpost\Api\Geo\DataObjects\ServicePoint;
use Webatvantage\Bpost\Api\Geo\Enums\PointType;
use Webatvantage\Bpost\Api\Geo\Exceptions\LocatorException;
use Webatvantage\Bpost\Api\Geo\GeoApiConfig;

/**
 * `Function=info` — the details of one pick-up point, by id and type.
 */
class ServicePointDetailsRequest extends GeoRequest
{
	public function __construct(
		HttpApiAdapter $apiAdapter,
		GeoApiConfig $config,
		string $id,
		PointType $type,
	) {
		parent::__construct($apiAdapter, [
			'Function' => 'info',
			'Partner' => $config->partner,
			'AppId' => $config->appId,
			'Id' => $id,
			'Type' => $type,
			'Country' => 'BE',
			'Language' => Language::NL->value,
		]);
	}

	public function country(string $country): static
	{
		return $this->addParameter('Country', strtoupper($country));
	}

	/**
	 * @throws UnexpectedValueException
	 */
	public function get(): ServicePoint
	{
		$xml = $this->send();

		$entry = $xml->child('Poi');
		$record = isset($entry) ? $entry->child('Record') : null;

		if ($entry === null || $record === null)
		{
			throw new LocatorException(
				'The Geolocator returned no point for this id.',
				200,
				(string)$xml->C14N(),
			);
		}

		$page = $entry->child('Page');

		return ServicePoint::fromXml(
			$record,
			null,
			isset($page) ? $page->attribute('ServiceRef') : null,
		);
	}
}
