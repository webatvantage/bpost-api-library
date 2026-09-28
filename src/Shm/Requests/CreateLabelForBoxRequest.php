<?php

namespace Webatvantage\Bpost\Api\Shm\Requests;

use Webatvantage\Bpost\Api\ApiAdapter\HttpApiAdapter;
use Webatvantage\Bpost\Api\Shm\Enums\LabelFormat;
use Webatvantage\Bpost\Api\Shm\Enums\LabelOutput;
use Webatvantage\Bpost\Api\Shm\ShmApiConfig;

/**
 * The label for one box, by its barcode.
 *
 * Only works on a box that is already PRINTED, and is meant for reprinting the original label.
 * Each box should still end up with exactly one label on it.
 */
class CreateLabelForBoxRequest extends CreateLabelRequest
{
	public function __construct(
		HttpApiAdapter $apiAdapter,
		ShmApiConfig $config,
		string $barcode,
		LabelFormat $format,
		LabelOutput $output,
		bool $withReturnLabels = false,
	) {
		parent::__construct(
			apiAdapter: $apiAdapter,
			config: $config,
			path: '/boxes/' . rawurlencode($barcode),
			format: $format,
			output: $output,
			withReturnLabels: $withReturnLabels,
		);
	}
}
