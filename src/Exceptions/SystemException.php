<?php

namespace Webatvantage\Bpost\Api\Exceptions;

/**
 * A bpost `<systemException>` document: something failed on bpost's side.
 *
 * The message carries the support token bpost generates; keep it when reporting the failure, it is
 * the only handle their support has on the incident.
 */
class SystemException extends ApiException {}
