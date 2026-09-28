<?php

namespace Webatvantage\Bpost\Api\Exceptions;

/**
 * A bpost `<businessException>` document: the request was understood and rejected on its merits,
 * for example confirming an order that is already cancelled.
 *
 * Fixable by changing the request, so the code and message are worth showing to the caller.
 */
final class BusinessException extends ApiException {}
