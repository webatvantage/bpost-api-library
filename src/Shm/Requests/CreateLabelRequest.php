<?php

namespace Webatvantage\Bpost\Api\Shm\Requests;

use Webatvantage\Bpost\Api\ApiAdapter\HttpApiAdapter;
use Webatvantage\Bpost\Api\Enums\Method;
use Webatvantage\Bpost\Api\Shm\DataObjects\Label;
use Webatvantage\Bpost\Api\Shm\Enums\LabelFormat;
use Webatvantage\Bpost\Api\Shm\Enums\LabelOutput;
use Webatvantage\Bpost\Api\Shm\ShmApiConfig;

/**
 * Shared by the three ways of asking for labels.
 *
 * The Content-Type is labelRequest-v5 while the Accept type stays at v3.4: bpost versions the
 * two separately.
 */
abstract class CreateLabelRequest extends ShmRequest
{
	public const string CONTENT_TYPE = 'application/vnd.bpost.shm-labelRequest-v5+XML';

	public function __construct(
		HttpApiAdapter $apiAdapter,
		ShmApiConfig $config,
		string $path,
		protected readonly LabelFormat $format,
		protected readonly LabelOutput $output,
		bool $withReturnLabels,
		Method $method = Method::GET,
		?string $body = null,
	) {
		$output->assertSupports($format);

		parent::__construct(
			apiAdapter: $apiAdapter,
			config: $config,
			method: $method,
			path: $path . '/labels/' . $format->value . ($withReturnLabels ? '/withReturnLabels' : ''),
			headers: [
				'Accept' => $output->acceptHeader(),
				'Content-Type' => static::CONTENT_TYPE,
			],
			body: $body,
		);
	}

	/**
	 * @return array<int, Label>
	 */
	public function get(): array
	{
		$xml = $this->sendExpectingXml();
		$labels = [];

		foreach ($xml->children('label') as $label)
		{
			$labels[] = Label::fromXml($label);
		}

		return $labels;
	}
}
