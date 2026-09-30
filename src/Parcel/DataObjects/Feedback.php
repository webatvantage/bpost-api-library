<?php

namespace Webatvantage\Bpost\Api\Parcel\DataObjects;

use Dom\XPath;
use Webatvantage\Bpost\Api\Contracts\XmlDeserializable;
use Webatvantage\Bpost\Api\Support\XmlElement;

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

	public static function fromXml(XmlElement $xml): static
	{
		$document = $xml->ownerDocument;

		// Matched on local name: bpost binds these to a namespace it does not always declare.
		$collect = static function (string $name) use ($xml, $document): array {
			$values = [];

			if ($document === null)
			{
				return $values;
			}

			foreach (new XPath($document)->query(sprintf('descendant-or-self::*[local-name()="%s"]', $name), $xml) as $node)
			{
				$value = trim($node->textContent ?? '');

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
