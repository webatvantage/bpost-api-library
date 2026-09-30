<?php

namespace Webatvantage\Bpost\Api\Shm\Requests;

use Webatvantage\Bpost\Api\ApiAdapter\HttpApiAdapter;
use Webatvantage\Bpost\Api\Enums\Method;
use Webatvantage\Bpost\Api\Exceptions\InvalidLengthException;
use Webatvantage\Bpost\Api\Exceptions\InvalidValueException;
use Webatvantage\Bpost\Api\Exceptions\UnexpectedValueException;
use Webatvantage\Bpost\Api\Shm\DataObjects\Order;
use Webatvantage\Bpost\Api\Shm\ShmApiConfig;

/**
 * GET /{accountId}/orders/{reference} — everything bpost holds under one reference.
 *
 * The Accept type is v3.5 rather than v5. bpost versions each operation separately, and this is
 * the version its own v5 example set documents for this call.
 */
class FetchOrderRequest extends ShmRequest
{
	public const string ACCEPT = 'application/vnd.bpost.shm-order-v3.5+XML';

	public function __construct(HttpApiAdapter $apiAdapter, ShmApiConfig $config, string $reference)
	{
		parent::__construct(
			apiAdapter: $apiAdapter,
			config: $config,
			method: Method::GET,
			path: '/orders/' . rawurlencode($reference),
			headers: ['Accept' => static::ACCEPT],
		);
	}

	/**
	 * @throws InvalidLengthException
	 * @throws InvalidValueException
	 * @throws UnexpectedValueException
	 */
	public function get(): Order
	{
		return Order::fromXml($this->sendExpectingXml());
	}
}
