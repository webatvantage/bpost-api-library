<?php

namespace Webatvantage\Bpost\Api\Shm\Requests;

use Webatvantage\Bpost\Api\ApiAdapter\HttpApiAdapter;
use Webatvantage\Bpost\Api\Enums\Method;
use Webatvantage\Bpost\Api\Shm\Enums\LabelFormat;
use Webatvantage\Bpost\Api\Shm\Enums\LabelOutput;
use Webatvantage\Bpost\Api\Shm\Enums\ShmNamespace;
use Webatvantage\Bpost\Api\Shm\ShmApiConfig;
use Webatvantage\Bpost\Api\Support\DefaultNamespace;
use Webatvantage\Bpost\Api\Support\XmlDocument;

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
		// batchLabels puts the global namespace in the default position, where an order document
		// puts the national one and writes global elements under tns:.
		$namespace = new DefaultNamespace(ShmNamespace::Global->uri());

		$document = XmlDocument::create();
		$batch = $document->root('batchLabels', $namespace);
		$batch->setAttributeNS(XmlDocument::XMLNS, 'xmlns:xsi', XmlDocument::XSI);
		$batch->setAttributeNS(XmlDocument::XSI, 'xsi:schemaLocation', ShmNamespace::Global->uri());

		foreach ($references as $reference)
		{
			$batch->appendText('order', $reference, $namespace);
		}

		parent::__construct(
			apiAdapter: $apiAdapter,
			config: $config,
			path: '',
			format: $format,
			output: $output,
			withReturnLabels: $withReturnLabels,
			method: Method::POST,
			body: $document->toString(),
		);
	}
}
