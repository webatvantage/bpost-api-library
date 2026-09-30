<?php

namespace Webatvantage\Bpost\Api\ApiAdapter;

use GuzzleHttp\TransferStats;
use GuzzleLogMiddleware\Handler\HandlerInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use Throwable;

readonly class ConditionalLogHandler implements HandlerInterface
{
	public function __construct(private HandlerInterface $handler, private string $loggingOptionName) {}

	/**
	 * @param array<string, mixed> $options
	 */
	public function log(
		LoggerInterface $logger,
		RequestInterface $request,
		?ResponseInterface $response = null,
		?Throwable $exception = null,
		?TransferStats $stats = null,
		array $options = [],
	): void {
		$loggingEnabled = $options[$this->loggingOptionName] ?? true;

		if ($loggingEnabled === true || static::isFailedResponse($response, $exception))
		{
			$this->handler->log($logger, $request, $response, $exception, $stats, $options);
		}
	}

	protected static function isFailedResponse(?ResponseInterface $response, ?Throwable $exception): bool
	{
		if (isset($exception))
		{
			return true;
		}

		if (is_null($response))
		{
			return true;
		}

		return $response->getStatusCode() >= 400;
	}
}
