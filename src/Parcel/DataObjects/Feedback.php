<?php

namespace Webatvantage\Bpost\Api\Parcel\DataObjects;

use SimpleXMLElement;
use Webatvantage\Bpost\Api\Contracts\XmlDeserializable;

/**
 * What bpost made of an announcement.
 *
 * A 201 does not mean the announcement was accepted cleanly: bpost returns warnings and errors in
 * the body, so this is worth reading rather than discarding.
 */
class Feedback implements XmlDeserializable
{
	/**
	 * @param array<int, string> $warnings
	 * @param array<int, string> $errors
	 */
	public function __construct(public private(set) array $warnings = [], public private(set) array $errors = []) {}

	public static function fromXml(SimpleXMLElement $xml): static
	{
		$collect = static function (string $name) use ($xml): array {
			$values = [];

			foreach ($xml->xpath(sprintf('//*[local-name()="%s"]', $name)) ?: [] as $node)
			{
				$value = trim((string)$node);

				if ($value !== '')
				{
					$values[] = $value;
				}
			}

			return $values;
		};

		return new static($collect('Warning'), $collect('Error'));
	}

	public function hasErrors(): bool
	{
		return count($this->errors) > 0;
	}

	public function hasWarnings(): bool
	{
		return count($this->warnings) > 0;
	}
}
