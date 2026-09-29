<?php

namespace Webatvantage\Bpost\Api\Parcel\Requests;

use Webatvantage\Bpost\Api\ApiAdapter\HttpApiAdapter;
use Webatvantage\Bpost\Api\Contracts\Request;
use Webatvantage\Bpost\Api\Enums\Method;
use Webatvantage\Bpost\Api\Exceptions\InvalidLengthException;
use Webatvantage\Bpost\Api\Exceptions\InvalidValueException;
use Webatvantage\Bpost\Api\Exceptions\UnserializableResponseException;
use Webatvantage\Bpost\Api\Parcel\DataObjects\ItemTracking;
use Webatvantage\Bpost\Api\Support\XmlElement;

/**
 * GET /services/trackedmail/item/{barcode}/trackingInfo — where a parcel has been.
 */
class FetchTrackingInfoRequest extends Request
{
	public function __construct(private readonly HttpApiAdapter $apiAdapter, string $barcode)
	{
		parent::__construct(
			method: Method::GET,
			resourceUri: '/services/trackedmail/item/' . rawurlencode($barcode) . '/trackingInfo',
		);
	}

	/**
	 * @throws InvalidLengthException
	 * @throws InvalidValueException
	 */
	public function get(): ItemTracking
	{
		$response = $this->apiAdapter->request($this);

		if (!$response instanceof XmlElement)
		{
			throw new UnserializableResponseException('The tracking service did not answer with XML.', 200, (string)$response);
		}

		return ItemTracking::fromXml($response);
	}
}
