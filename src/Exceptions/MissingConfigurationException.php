<?php

namespace Webatvantage\Bpost\Api\Exceptions;

final class MissingConfigurationException extends BpostException
{
	public static function forDomain(string $domain): self
	{
		return new self(sprintf(
			'No configuration was given for the "%s" API, so it cannot be used. Pass a %sApiConfig '
			. 'to BpostApiConfig, or construct the %sApiClient directly.',
			$domain,
			ucfirst($domain),
			ucfirst($domain),
		));
	}
}
