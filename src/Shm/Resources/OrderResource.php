<?php

namespace Webatvantage\Bpost\Api\Shm\Resources;

use Webatvantage\Bpost\Api\ApiAdapter\HttpApiAdapter;
use Webatvantage\Bpost\Api\Contracts\Resource;
use Webatvantage\Bpost\Api\Shm\DataObjects\Order;
use Webatvantage\Bpost\Api\Shm\Enums\BoxStatus;
use Webatvantage\Bpost\Api\Shm\Requests\CreateOrderRequest;
use Webatvantage\Bpost\Api\Shm\Requests\FetchOrderRequest;
use Webatvantage\Bpost\Api\Shm\Requests\UpdateOrderStatusRequest;
use Webatvantage\Bpost\Api\Shm\ShmApiConfig;

class OrderResource extends Resource
{
	public function __construct(HttpApiAdapter $apiAdapter, private readonly ShmApiConfig $config)
	{
		parent::__construct($apiAdapter);
	}

	/**
	 * Create the order, or add its boxes to one that already carries the same reference.
	 */
	public function create(Order $order): void
	{
		$this->prepare(new CreateOrderRequest($this->apiAdapter, $this->config, $order))->send();
	}

	public function get(string $reference): Order
	{
		return $this->prepare(new FetchOrderRequest($this->apiAdapter, $this->config, $reference))->get();
	}

	/**
	 * Move every unprinted box in the order to a new status. Printed boxes are left alone.
	 */
	public function updateStatus(string $reference, BoxStatus $status): void
	{
		$this->prepare(new UpdateOrderStatusRequest($this->apiAdapter, $this->config, $reference, $status))->send();
	}
}
