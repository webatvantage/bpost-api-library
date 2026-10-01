<?php

namespace Webatvantage\Bpost\Api;

use GuzzleLogMiddleware\Handler\HandlerInterface;
use Webatvantage\Bpost\Api\Geo\GeoApiConfig;
use Webatvantage\Bpost\Api\Parcel\ParcelApiConfig;
use Webatvantage\Bpost\Api\Shm\ShmApiConfig;

/**
 * Credentials for the bpost services you use, and what they share.
 *
 * Every service is optional: bpost issues their credentials separately and most integrations only
 * hold one or two sets. Reaching an unconfigured service throws rather than failing at the HTTP
 * layer with something unhelpful.
 */
readonly class BpostApiConfig
{
	/** The Guzzle request option a call's logging choice travels in, for a log handler of your own to read. */
	public const string LOGGING_OPTION_NAME = 'bpost_logging';

	/**
	 * @param HandlerInterface|null $logHandler What a log record is made of, for every service, and
	 *                                          which calls are worth one. Left out, records are the
	 *                                          array shape with a level per status range
	 */
	public function __construct(
		public ?ShmApiConfig $shm = null,
		public ?GeoApiConfig $geo = null,
		public ?ParcelApiConfig $parcel = null,
		public ?HandlerInterface $logHandler = null,
	) {}
}
