<?php

namespace Webatvantage\Bpost\Api\Resources;

use Closure;
use Webatvantage\Bpost\Api\ApiAdapter\HttpApiAdapter;

abstract class Resource
{
	public function __construct(protected readonly HttpApiAdapter $apiAdapter) {}

	public function debug(?Closure $callback): static
	{
		$this->apiAdapter->setDebugCallback($callback);

		return $this;
	}
}
