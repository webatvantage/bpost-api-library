<?php

namespace Bpost\BpostApiClient\Exception\BpostLogicException;

use Bpost\BpostApiClient\Exception\BpostLogicException;
use Exception;

/**
 * Class BpostMultipleCommunicationMethodException
 */
class BpostMultipleCommunicationMethodException extends BpostLogicException
{
	/**
	 * BpostMultipleCommunicationMethodException constructor.
	 *
	 * @param string $nameEntry
	 * @param string $optionA
	 * @param string $optionB
	 * @param int $code
	 * @param Exception|null $previous
	 */
	public function __construct($nameEntry, string $optionA, string $optionB, $code = 0, ?Exception $previous = null)
	{
		$message = sprintf(
			'Only one of %1$s or %2$s can be set for %3$s, not both.',
			$optionA,
			$optionB,
			$nameEntry,
		);
		parent::__construct($message, $code, $previous);
	}
}
