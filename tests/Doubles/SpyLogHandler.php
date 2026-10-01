<?php

namespace Webatvantage\Bpost\Api\Tests\Doubles;

use GuzzleHttp\TransferStats;
use GuzzleLogMiddleware\Handler\HandlerInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use Throwable;
use Webatvantage\Bpost\Api\BpostApiConfig;

/**
 * Records the logging choice each call carried, which is what a handler of your own acts on.
 */
class SpyLogHandler implements HandlerInterface
{
	/** @var array<int, bool|null> */
	public array $choices = [];

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
		$choice = $options[BpostApiConfig::LOGGING_OPTION_NAME] ?? null;

		$this->choices[] = is_bool($choice) ? $choice : null;
	}

	/**
	 * The choice the most recent call carried, or null when it carried none.
	 */
	public function lastChoice(): ?bool
	{
		return $this->choices[count($this->choices) - 1] ?? null;
	}
}
