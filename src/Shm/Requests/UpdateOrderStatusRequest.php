<?php

namespace Webatvantage\Bpost\Api\Shm\Requests;

use Webatvantage\Bpost\Api\ApiAdapter\HttpApiAdapter;
use Webatvantage\Bpost\Api\Enums\Method;
use Webatvantage\Bpost\Api\Exceptions\InvalidValueException;
use Webatvantage\Bpost\Api\Shm\Enums\BoxStatus;
use Webatvantage\Bpost\Api\Shm\ShmApiConfig;
use Webatvantage\Bpost\Api\Shm\Support\Xml;

/**
 * POST /{accountId}/orders/{reference} — set the status of every unprinted box in an order.
 *
 * Printed boxes are left alone. The payload is still on the v3 namespace, which bpost never moved.
 */
class UpdateOrderStatusRequest extends ShmRequest
{
	public const string CONTENT_TYPE = 'application/vnd.bpost.shm-orderUpdate-v3+XML';

	/**
	 * @throws InvalidValueException
	 */
	public function __construct(
		HttpApiAdapter $apiAdapter,
		ShmApiConfig $config,
		string $reference,
		BoxStatus $status,
	) {
		if (!$status->isSettable())
		{
			throw new InvalidValueException('status', $status->value, [
				BoxStatus::Open->value,
				BoxStatus::Cancelled->value,
				BoxStatus::OnHold->value,
			]);
		}

		// Still on the v3 namespace, and it is this document's default — unlike an order, which
		// defaults to the national namespace.
		$document = Xml::document();
		$update = $document->createElementNS(Xml::READ_GLOBAL, 'orderUpdate');
		$update->setAttributeNS(Xml::XMLNS, 'xmlns:xsi', Xml::XSI);
		$update->setAttributeNS(Xml::XSI, 'xsi:schemaLocation', Xml::READ_GLOBAL);

		$state = $document->createElementNS(Xml::READ_GLOBAL, 'status');
		$state->textContent = $status->value;
		$update->append($state);
		$document->append($update);

		parent::__construct(
			apiAdapter: $apiAdapter,
			config: $config,
			method: Method::POST,
			path: '/orders/' . rawurlencode($reference),
			headers: ['Content-Type' => self::CONTENT_TYPE],
			body: Xml::toString($document),
			expectsXml: false,
		);
	}

	public function send(): void
	{
		$this->apiAdapter->request($this);
	}
}
