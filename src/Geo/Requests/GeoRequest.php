<?php

namespace Webatvantage\Bpost\Api\Geo\Requests;

use Webatvantage\Bpost\Api\ApiAdapter\HttpApiAdapter;
use Webatvantage\Bpost\Api\Contracts\Request;
use Webatvantage\Bpost\Api\Enums\Method;
use Webatvantage\Bpost\Api\Geo\Exceptions\LocatorException;
use Webatvantage\Bpost\Api\Support\XmlElement;

/**
 * Base for the four Geolocator operations.
 *
 * Every one of them is a GET on the same `/Locator` path, distinguished only by `Function`, and
 * every one of them reports failure in the body rather than the status code.
 */
abstract class GeoRequest extends Request
{
	/**
	 * @param array<string, mixed> $parameters
	 */
	public function __construct(protected readonly HttpApiAdapter $apiAdapter, array $parameters)
	{
		parent::__construct(Method::GET, '/Locator', $parameters);
	}

	/**
	 * Include the box number of each point in the response.
	 */
	public function withBoxNumber(bool $include = true): static
	{
		return $this->addParameter('IncludeBoxNumber', $include);
	}

	/**
	 * Include the locker attributes. Only meaningful for parcel lockers in Belgium.
	 */
	public function withAttributes(bool $include = true): static
	{
		return $this->addParameter('IncludeAttributes', $include);
	}

	protected function send(): XmlElement
	{
		$xml = $this->apiAdapter->request($this);

		if (!$xml instanceof XmlElement)
		{
			throw new LocatorException('The Geolocator did not answer with XML.', 200, $xml);
		}

		if ($xml->attribute('type') === 'TaxipostLocatorError')
		{
			throw new LocatorException(
				$xml->text('txt') ?? 'The Geolocator rejected the request.',
				(int)$xml->text('status'),
				(string)$xml->C14N(),
			);
		}

		return $xml;
	}
}
