<?php

namespace Webatvantage\Bpost\Api\Shm\Requests;

use Webatvantage\Bpost\Api\ApiAdapter\HttpApiAdapter;
use Webatvantage\Bpost\Api\Contracts\Request;
use Webatvantage\Bpost\Api\Enums\Method;
use Webatvantage\Bpost\Api\Exceptions\ApiException;
use Webatvantage\Bpost\Api\Exceptions\TransporterException;
use Webatvantage\Bpost\Api\Exceptions\UnserializableResponseException;
use Webatvantage\Bpost\Api\Shm\ShmApiConfig;
use Webatvantage\Bpost\Api\Support\XmlElement;

/**
 * Base for the Shipping Manager operations.
 *
 * Every URL starts with the account id, which is also the Basic auth username — bpost checks the
 * two match and answers 401 when they do not.
 */
abstract class ShmRequest extends Request
{
	/**
	 * @param array<string, string> $headers
	 */
	public function __construct(
		protected readonly HttpApiAdapter $apiAdapter,
		protected readonly ShmApiConfig $config,
		Method $method,
		string $path,
		array $headers = [],
		?string $body = null,
		bool $expectsXml = true,
	) {
		parent::__construct(
			method: $method,
			resourceUri: '/' . $config->accountId . $path,
			body: $body,
			headers: $headers,
			expectsXml: $expectsXml,
		);
	}

	/**
	 * @throws UnserializableResponseException
	 * @throws TransporterException
	 * @throws ApiException
	 */
	protected function sendExpectingXml(): XmlElement
	{
		$response = $this->apiAdapter->request($this);

		if (!$response instanceof XmlElement)
		{
			throw new UnserializableResponseException('The Shipping Manager did not answer with XML.', 200, (string)$response);
		}

		return $response;
	}
}
