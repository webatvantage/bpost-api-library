<?php

namespace Webatvantage\Bpost\Api\Shm\Resources;

use Webatvantage\Bpost\Api\ApiAdapter\HttpApiAdapter;
use Webatvantage\Bpost\Api\Contracts\Resource;
use Webatvantage\Bpost\Api\Shm\DataObjects\ProductConfiguration\ProductConfiguration;
use Webatvantage\Bpost\Api\Shm\Requests\FetchProductConfigurationRequest;
use Webatvantage\Bpost\Api\Shm\ShmApiConfig;

class ProductConfigurationResource extends Resource
{
	public function __construct(HttpApiAdapter $apiAdapter, private readonly ShmApiConfig $config)
	{
		parent::__construct($apiAdapter);
	}

	public function get(): ProductConfiguration
	{
		return $this->prepare(new FetchProductConfigurationRequest($this->apiAdapter, $this->config))->get();
	}
}
