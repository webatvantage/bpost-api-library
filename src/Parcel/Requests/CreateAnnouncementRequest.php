<?php

namespace Webatvantage\Bpost\Api\Parcel\Requests;

use SimpleXMLElement;
use Webatvantage\Bpost\Api\ApiAdapter\HttpApiAdapter;
use Webatvantage\Bpost\Api\Contracts\Request;
use Webatvantage\Bpost\Api\Enums\Method;
use Webatvantage\Bpost\Api\Parcel\DataObjects\Announcement;
use Webatvantage\Bpost\Api\Parcel\DataObjects\Feedback;
use Webatvantage\Bpost\Api\Parcel\ParcelApiConfig;
use Webatvantage\Bpost\Api\Parcel\Support\Xml;

/**
 * POST /services/trackedmail/announcement — tell bpost a parcel is coming.
 *
 * Answers 201 with a feedback document, which carries any warnings or errors, so a 201 alone does
 * not mean the announcement was clean.
 */
class CreateAnnouncementRequest extends Request
{
	public const string CONTENT_TYPE = 'application/vnd.bpost.announcement-v1+XML;charset=UTF-8';

	public function __construct(
		private readonly HttpApiAdapter $apiAdapter,
		ParcelApiConfig $config,
		Announcement $announcement,
	) {
		$document = Xml::document();
		$document->appendChild($announcement->toXml($document, $config->accountId));

		parent::__construct(
			method: Method::POST,
			resourceUri: '/services/trackedmail/announcement',
			body: Xml::toString($document),
			headers: ['Content-Type' => self::CONTENT_TYPE],
		);
	}

	public function send(): Feedback
	{
		$response = $this->apiAdapter->request($this);

		if (!$response instanceof SimpleXMLElement)
		{
			return new Feedback();
		}

		return Feedback::fromXml($response);
	}
}
