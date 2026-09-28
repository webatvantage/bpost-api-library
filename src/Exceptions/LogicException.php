<?php

namespace Webatvantage\Bpost\Api\Exceptions;

/**
 * A value rejected before any request went out.
 *
 * Distinct from ApiException: nothing was sent, so nothing needs undoing.
 */
class LogicException extends BpostException {}
