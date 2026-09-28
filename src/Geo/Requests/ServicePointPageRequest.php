<?php

namespace Webatvantage\Bpost\Api\Geo\Requests;

use Webatvantage\Bpost\Api\Contracts\Request;
use Webatvantage\Bpost\Api\Enums\Language;
use Webatvantage\Bpost\Api\Enums\Method;
use Webatvantage\Bpost\Api\Geo\Enums\PointType;
use Webatvantage\Bpost\Api\Geo\GeoApiConfig;

/**
 * `Function=page` — the HTML details page for one point.
 *
 * This one is never sent: bpost answers HTML meant for a browser or an iframe, so the library only
 * builds the URL. It still needs Function, Partner and AppId.
 */
class ServicePointPageRequest extends Request
{
	public function __construct(GeoApiConfig $config, string $id, PointType $type)
	{
		parent::__construct(Method::GET, '/Locator', [
			'Function' => 'page',
			'Partner' => $config->partner,
			'AppId' => $config->appId,
			'Id' => $id,
			'Type' => $type,
			'Language' => Language::NL->value,
		], expectsXml: false);
	}

	public function language(Language $language): static
	{
		return $this->addParameter('Language', $language);
	}

	public function withAttributes(bool $include = true): static
	{
		return $this->addParameter('IncludeAttributes', $include);
	}

	public function toUrl(string $baseUri): string
	{
		return rtrim($baseUri, '/') . $this->getUri();
	}
}
