<?php

namespace Webatvantage\Bpost\Api\Support;

use DOMDocument;
use DOMElement;
use SimpleXMLElement;

/**
 * DOM primitives shared by every domain.
 *
 * Namespace URIs and prefixed element names are deliberately *not* here — those differ per bpost
 * service and belong to that service's own support class.
 */
class Xml
{
	public static function document(): DOMDocument
	{
		$document = new DOMDocument('1.0', 'UTF-8');
		$document->preserveWhiteSpace = false;
		$document->formatOutput = true;

		return $document;
	}

	/**
	 * Qualify a tag name with a namespace prefix, when one applies.
	 */
	public static function prefixed(string $tagName, ?string $prefix = null): string
	{
		if ($prefix === null || $prefix === '')
		{
			return $tagName;
		}

		return $prefix . ':' . $tagName;
	}

	/**
	 * Create an element holding a text value.
	 *
	 * The value goes in through a text node rather than the DOMElement constructor, so that `&`,
	 * `<` and `>` are escaped. bpost rejects a document where they are not — a receiver named
	 * "Dupont & Fils" is enough to break the request.
	 */
	public static function createTextElement(
		DOMDocument $document,
		string $tagName,
		string|int|float|bool $value,
	): DOMElement {
		$element = $document->createElement($tagName);
		$element->appendChild($document->createTextNode(self::stringify($value)));

		return $element;
	}

	/**
	 * Append a text element under a prefix, skipping it when the value is null or empty.
	 *
	 * Most bpost elements are optional and an empty one is not the same as an absent one — its own
	 * examples carry the note "When box is empty this tag has to be removed" — so this guard is
	 * repeated often enough to be worth centralising.
	 */
	public static function appendText(
		DOMDocument $document,
		DOMElement $parent,
		string $tagName,
		string|int|float|bool|null $value,
		?string $prefix = null,
	): void {
		if ($value === null || $value === '')
		{
			return;
		}

		$parent->appendChild(self::createTextElement($document, self::prefixed($tagName, $prefix), $value));
	}

	/**
	 * The children of an element, preferring a namespace but not insisting on it.
	 *
	 * Several of bpost's own example responses use prefixes they never declare, so a namespaced
	 * lookup finds nothing in them. Falling back to the default children keeps those parseable.
	 */
	public static function readChildren(SimpleXMLElement $xml, string $namespace): SimpleXMLElement
	{
		$children = $xml->children($namespace);

		return count($children) > 0 ? $children : $xml->children();
	}

	public static function toString(DOMDocument $document): string
	{
		return (string)$document->saveXML();
	}

	/**
	 * Parse a response body, returning null when it is not well-formed XML.
	 *
	 * bpost occasionally answers with an HTML error page or a bare text/plain message, so callers
	 * decide what an unparsable body means rather than getting a warning raised at them here.
	 */
	public static function tryParse(string $body): ?SimpleXMLElement
	{
		if (trim($body) === '')
		{
			return null;
		}

		$previous = libxml_use_internal_errors(true);
		$xml = simplexml_load_string($body);
		libxml_clear_errors();
		libxml_use_internal_errors($previous);

		return $xml === false ? null : $xml;
	}

	private static function stringify(string|int|float|bool $value): string
	{
		if (is_bool($value))
		{
			return $value ? 'true' : 'false';
		}

		return (string)$value;
	}
}
