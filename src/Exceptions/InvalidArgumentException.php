<?php

namespace Webatvantage\Bpost\Api\Exceptions;

/**
 * A value the caller set, rejected before any request went out.
 *
 * Distinct from ApiException: nothing was sent, so nothing needs undoing.
 */
class InvalidArgumentException extends BpostException {}
