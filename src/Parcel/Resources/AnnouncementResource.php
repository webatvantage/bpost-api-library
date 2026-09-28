<?php

namespace Webatvantage\Bpost\Api\Parcel\Resources;

use Webatvantage\Bpost\Api\ApiAdapter\HttpApiAdapter;
use Webatvantage\Bpost\Api\Parcel\DataObjects\Announcement;
use Webatvantage\Bpost\Api\Parcel\DataObjects\Feedback;
use Webatvantage\Bpost\Api\Parcel\ParcelApiConfig;
use Webatvantage\Bpost\Api\Parcel\Requests\CreateAnnouncementRequest;
use Webatvantage\Bpost\Api\Resources\Resource;

class AnnouncementResource extends Resource
{
	public function __construct(HttpApiAdapter $apiAdapter, private readonly ParcelApiConfig $config)
	{
		parent::__construct($apiAdapter);
	}

	/**
	 * Announce a parcel. Send this before the parcel reaches bpost, or the messaging and
	 * value-added services it names cannot be acted on.
	 */
	public function create(Announcement $announcement): Feedback
	{
		return new CreateAnnouncementRequest($this->apiAdapter, $this->config, $announcement)->send();
	}
}
