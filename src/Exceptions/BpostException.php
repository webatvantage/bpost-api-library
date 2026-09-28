<?php

namespace Webatvantage\Bpost\Api\Exceptions;

use Exception;

/**
 * Root of every exception this library throws.
 *
 * Consumers catch this to mean "the bpost integration failed" without caring how.
 */
class BpostException extends Exception {}
