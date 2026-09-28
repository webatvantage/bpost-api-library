<?php

namespace Webatvantage\Bpost\Api\Shm\Requests;

use Webatvantage\Bpost\Api\ApiAdapter\HttpApiAdapter;
use Webatvantage\Bpost\Api\Shm\Enums\LabelFormat;
use Webatvantage\Bpost\Api\Shm\Enums\LabelOutput;
use Webatvantage\Bpost\Api\Shm\ShmApiConfig;

/**
 * Labels for every unprinted box in an order.
 *
 * The boxes it returns move to PRINTED; ones already printed are left alone and not returned.
 */
class CreateLabelForOrderRequest extends CreateLabelRequest
{
	public function __construct(
		HttpApiAdapter $apiAdapter,
		ShmApiConfig $config,
		string $reference,
		LabelFormat $format,
		LabelOutput $output,
		bool $withReturnLabels = false,
	) {
		parent::__construct(
			apiAdapter: $apiAdapter,
			config: $config,
			path: '/orders/' . rawurlencode($reference),
			format: $format,
			output: $output,
			withReturnLabels: $withReturnLabels,
		);
	}
}
