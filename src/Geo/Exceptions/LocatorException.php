<?php

namespace Webatvantage\Bpost\Api\Geo\Exceptions;

use Webatvantage\Bpost\Api\Exceptions\ApiException;

/**
 * The Geolocator reported a problem.
 *
 * It answers HTTP 200 and signals failure inside the document instead, as
 * `<TaxipostLocator type="TaxipostLocatorError">`, so this cannot be detected from the status code.
 */
final class LocatorException extends ApiException {}
