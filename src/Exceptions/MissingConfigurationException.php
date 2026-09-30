<?php

namespace Webatvantage\Bpost\Api\Exceptions;

class MissingConfigurationException extends BpostException
{
	public static function forDomain(string $domain): static
	{
		$message = 'No configuration was given for the "%s" API, so it cannot be used. Pass a %sApiConfig to BpostApiConfig, or construct the %sApiClient directly.';

		return new static(sprintf($message, $domain, ucfirst($domain), ucfirst($domain)));
	}
}
