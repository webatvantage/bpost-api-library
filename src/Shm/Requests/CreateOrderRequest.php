<?php

namespace Webatvantage\Bpost\Api\Shm\Requests;

use Webatvantage\Bpost\Api\ApiAdapter\HttpApiAdapter;
use Webatvantage\Bpost\Api\Enums\Method;
use Webatvantage\Bpost\Api\Shm\DataObjects\Order;
use Webatvantage\Bpost\Api\Shm\ShmApiConfig;
use Webatvantage\Bpost\Api\Shm\Support\Xml;

/**
 * POST /{accountId}/orders — create an order, or add boxes to one that already exists.
 *
 * bpost answers 201 with an empty body and the new order's location in a header.
 */
class CreateOrderRequest extends ShmRequest
{
	public const string CONTENT_TYPE = 'application/vnd.bpost.shm-order-v5+XML';

	public function __construct(HttpApiAdapter $apiAdapter, ShmApiConfig $config, Order $order)
	{
		$document = Xml::document();
		$document->appendChild($order->toXml($document, $config->accountId));

		parent::__construct(
			apiAdapter: $apiAdapter,
			config: $config,
			method: Method::POST,
			path: '/orders',
			headers: ['Content-Type' => self::CONTENT_TYPE],
			body: Xml::toString($document),
			expectsXml: false,
		);
	}

	public function send(): void
	{
		$this->apiAdapter->request($this);
	}
}
