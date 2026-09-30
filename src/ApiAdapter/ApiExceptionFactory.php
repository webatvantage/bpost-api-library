<?php

namespace Webatvantage\Bpost\Api\ApiAdapter;

use Dom\XPath;
use Webatvantage\Bpost\Api\Exceptions\ApiException;
use Webatvantage\Bpost\Api\Exceptions\BusinessException;
use Webatvantage\Bpost\Api\Exceptions\InvalidResponseException;
use Webatvantage\Bpost\Api\Exceptions\SystemException;
use Webatvantage\Bpost\Api\Support\XmlDocument;
use Webatvantage\Bpost\Api\Support\XmlElement;

/**
 * Maps a bpost fault document onto an exception
 */
class ApiExceptionFactory
{
	protected const int MAX_BODY_LENGTH = 500;

	public static function fromResponse(int $statusCode, string $body): ApiException
	{
		$xml = XmlDocument::tryParse($body);

		if ($xml === null)
		{
			return new InvalidResponseException(static::fallbackMessage($statusCode, $body), $statusCode, $body);
		}

		$message = static::firstValue($xml, 'message') ?? static::fallbackMessage($statusCode, $body);
		$code = static::firstValue($xml, 'code');

		return match ($xml->localName)
		{
			'businessException' => new BusinessException($message, (int)($code ?? $statusCode), $body),
			'systemException' => new SystemException($message, $statusCode, $body),
			default => new InvalidResponseException($message, $statusCode, $body),
		};
	}

	/**
	 * bpost's fault documents bind `code` and `message` to a different namespace than their own
	 * root element, and which namespace varies between the business and system shapes, so they are
	 * matched on local name.
	 */
	protected static function firstValue(XmlElement $xml, string $localName): ?string
	{
		$document = $xml->ownerDocument;

		if ($document === null)
		{
			return null;
		}

		$found = new XPath($document)->query(sprintf('descendant-or-self::*[local-name()="%s"]', $localName), $xml);
		$first = $found->item(0);

		if ($first === null)
		{
			return null;
		}

		$value = trim($first->textContent ?? '');

		return $value === '' ? null : $value;
	}

	protected static function fallbackMessage(int $statusCode, string $body): string
	{
		$body = trim($body);

		if ($body === '')
		{
			return sprintf('bpost answered HTTP %d with an empty body.', $statusCode);
		}

		if (mb_strlen($body) > static::MAX_BODY_LENGTH)
		{
			$body = mb_substr($body, 0, static::MAX_BODY_LENGTH) . '…';
		}

		return sprintf('bpost answered HTTP %d: %s', $statusCode, $body);
	}
}
