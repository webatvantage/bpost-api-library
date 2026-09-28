<?php

namespace Webatvantage\Bpost\Api\Shm\Resources;

use Webatvantage\Bpost\Api\ApiAdapter\HttpApiAdapter;
use Webatvantage\Bpost\Api\Contracts\Resource;
use Webatvantage\Bpost\Api\Shm\Enums\LabelFormat;
use Webatvantage\Bpost\Api\Shm\Enums\LabelOutput;
use Webatvantage\Bpost\Api\Shm\Requests\CreateLabelForBoxRequest;
use Webatvantage\Bpost\Api\Shm\Requests\CreateLabelForOrderRequest;
use Webatvantage\Bpost\Api\Shm\Requests\CreateLabelInBulkRequest;
use Webatvantage\Bpost\Api\Shm\ShmApiConfig;

/**
 * The three ways of asking for labels.
 *
 * Each returns a request you narrow and then call get() on, so the format, output and whether
 * return labels are wanted read as a sentence rather than four positional arguments.
 */
class LabelResource extends Resource
{
	public function __construct(HttpApiAdapter $apiAdapter, private readonly ShmApiConfig $config)
	{
		parent::__construct($apiAdapter);
	}

	public function forOrder(
		string $reference,
		LabelFormat $format = LabelFormat::A6,
		LabelOutput $output = LabelOutput::Pdf,
		bool $withReturnLabels = false,
	): CreateLabelForOrderRequest {
		return new CreateLabelForOrderRequest(
			apiAdapter: $this->apiAdapter,
			config: $this->config,
			reference: $reference,
			format: $format,
			output: $output,
			withReturnLabels: $withReturnLabels,
		);
	}

	public function forBox(
		string $barcode,
		LabelFormat $format = LabelFormat::A6,
		LabelOutput $output = LabelOutput::Pdf,
		bool $withReturnLabels = false,
	): CreateLabelForBoxRequest {
		return new CreateLabelForBoxRequest(
			apiAdapter: $this->apiAdapter,
			config: $this->config,
			barcode: $barcode,
			format: $format,
			output: $output,
			withReturnLabels: $withReturnLabels,
		);
	}

	/**
	 * @param array<int, string> $references
	 */
	public function inBulk(
		array $references,
		LabelFormat $format = LabelFormat::A6,
		LabelOutput $output = LabelOutput::Pdf,
		bool $withReturnLabels = false,
	): CreateLabelInBulkRequest {
		return new CreateLabelInBulkRequest(
			apiAdapter: $this->apiAdapter,
			config: $this->config,
			references: $references,
			format: $format,
			output: $output,
			withReturnLabels: $withReturnLabels,
		);
	}
}
