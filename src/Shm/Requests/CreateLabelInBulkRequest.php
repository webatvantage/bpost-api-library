<?php

namespace Webatvantage\Bpost\Api\Shm\Requests;

use Webatvantage\Bpost\Api\ApiAdapter\HttpApiAdapter;
use Webatvantage\Bpost\Api\Enums\Method;
use Webatvantage\Bpost\Api\Shm\Enums\LabelFormat;
use Webatvantage\Bpost\Api\Shm\Enums\LabelOutput;
use Webatvantage\Bpost\Api\Shm\ShmApiConfig;
use Webatvantage\Bpost\Api\Shm\Support\Xml;

/**
 * Labels for the unprinted boxes of several orders at once.
 *
 * Sent as POST. The manual says GET while also saying the order references go in the body, which
 * cannot both be taken literally; POST is what the library has used against the live API for
 * years, so it stays until a live run says otherwise.
 */
class CreateLabelInBulkRequest extends CreateLabelRequest
{
	/**
	 * @param array<int, string> $references
	 */
	public function __construct(
		HttpApiAdapter $apiAdapter,
		ShmApiConfig $config,
		array $references,
		LabelFormat $format,
		LabelOutput $output,
		bool $withReturnLabels = false,
	) {
		// Not the order namespace map: batchLabels defaults to the global namespace, where an
		// order document defaults to the national one.
		$document = Xml::document();
		$batch = $document->createElementNS(Xml::WRITE_GLOBAL, 'batchLabels');
		$batch->setAttributeNS(Xml::XMLNS, 'xmlns:xsi', Xml::XSI);
		$batch->setAttributeNS(Xml::XSI, 'xsi:schemaLocation', Xml::WRITE_GLOBAL);

		foreach ($references as $reference)
		{
			$order = $document->createElementNS(Xml::WRITE_GLOBAL, 'order');
			$order->textContent = $reference;
			$batch->append($order);
		}

		$document->append($batch);

		parent::__construct(
			apiAdapter: $apiAdapter,
			config: $config,
			path: '',
			format: $format,
			output: $output,
			withReturnLabels: $withReturnLabels,
			method: Method::POST,
			body: Xml::toString($document),
		);
	}
}
