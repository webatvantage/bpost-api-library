<?php

namespace Webatvantage\Bpost\Api\Shm\Requests;

use Webatvantage\Bpost\Api\ApiAdapter\HttpApiAdapter;
use Webatvantage\Bpost\Api\Enums\Method;
use Webatvantage\Bpost\Api\Shm\DataObjects\ProductConfiguration\ProductConfiguration;
use Webatvantage\Bpost\Api\Shm\ShmApiConfig;

/**
 * GET /{accountId}/productconfig — the products, prices and options this account may use.
 *
 * Worth reading before building an order: an order naming a product the account is not configured
 * for is refused.
 */
class FetchProductConfigurationRequest extends ShmRequest
{
	public const string ACCEPT = 'application/vnd.bpost.shm-productConfiguration-v3.1+XML';

	public function __construct(HttpApiAdapter $apiAdapter, ShmApiConfig $config)
	{
		parent::__construct($apiAdapter, $config, Method::GET, '/productconfig', ['Accept' => static::ACCEPT]);
	}

	public function get(): ProductConfiguration
	{
		return ProductConfiguration::fromXml($this->sendExpectingXml());
	}
}
