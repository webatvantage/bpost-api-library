<?php

namespace Webatvantage\Bpost\Api\Support;

use Dom\Element;
use Dom\XMLDocument;
use DOMException;
use ValueError;

/**
 * DOM primitives shared by every domain.
 *
 * Namespace URIs and prefixed element names are deliberately *not* here — those differ per bpost
 * service and belong to that service's own support class, which overrides namespaceFor().
 */
class Xml
{
	/** Addresses, parties and options are written under this prefix by every bpost service. */
	public const string PREFIX_COMMON = 'common';

	public const string XSI = 'http://www.w3.org/2001/XMLSchema-instance';

	/** Namespace declarations are attributes in this namespace, not plain ones. */
	public const string XMLNS = 'http://www.w3.org/2000/xmlns/';

	public static function document(): XMLDocument
	{
		$document = XMLDocument::createEmpty(version: '1.0', encoding: 'UTF-8');
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
	 * Create an element in the namespace its prefix is declared under.
	 *
	 * A prefix the service does not map raises a namespace error here rather than producing a
	 * document bpost rejects for reasons it will not explain.
	 */
	public static function element(XMLDocument $document, string $tagName, ?string $prefix = null): Element
	{
		return $document->createElementNS(static::namespaceFor($prefix), self::prefixed($tagName, $prefix));
	}

	/**
	 * Create an element holding a text value.
	 *
	 * textContent escapes `&`, `<` and `>`. bpost rejects a document where they are not — a
	 * receiver named "Dupont & Fils" is enough to break the request.
	 */
	public static function createTextElement(
		XMLDocument $document,
		string $tagName,
		string|int|float|bool $value,
		?string $prefix = null,
	): Element {
		$element = static::element($document, $tagName, $prefix);
		$element->textContent = self::stringify($value);

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
		XMLDocument $document,
		Element $parent,
		string $tagName,
		string|int|float|bool|null $value,
		?string $prefix = null,
	): void {
		if ($value === null || $value === '')
		{
			return;
		}

		$parent->append(static::createTextElement($document, $tagName, $value, $prefix));
	}

	/**
	 * The first child element with one of these local names, or null.
	 *
	 * Matching ignores the namespace: several of bpost's own example responses use prefixes they
	 * never declare, and the Geolocator spells the same field differently per operation, so a
	 * caller passes every spelling it knows.
	 */
	public static function child(Element $element, string ...$localNames): ?Element
	{
		foreach ($localNames as $localName)
		{
			foreach ($element->children as $child)
			{
				if ($child->localName === $localName)
				{
					return $child;
				}
			}
		}

		return null;
	}

	/**
	 * Every child element with this local name, in document order.
	 *
	 * @return array<Element>
	 */
	public static function children(Element $element, string $localName): array
	{
		$found = [];

		foreach ($element->children as $child)
		{
			if ($child->localName === $localName)
			{
				$found[] = $child;
			}
		}

		return $found;
	}

	/**
	 * The trimmed text of the first child element with one of these local names.
	 *
	 * An element that is present but blank reads as null, because bpost sends both to mean absent.
	 */
	public static function text(Element $element, string ...$localNames): ?string
	{
		$child = self::child($element, ...$localNames);

		if ($child === null)
		{
			return null;
		}

		$value = trim($child->textContent);

		return $value === '' ? null : $value;
	}

	public static function attribute(Element $element, string $name): ?string
	{
		$value = $element->getAttribute($name);

		return $value === null || trim($value) === '' ? null : trim($value);
	}

	public static function integerAttribute(Element $element, string $name): ?int
	{
		$value = self::attribute($element, $name);

		return $value === null ? null : (int)$value;
	}

	public static function toString(XMLDocument $document): string
	{
		return (string)$document->saveXml();
	}

	/**
	 * Parse a response body, returning null when it is not well-formed XML.
	 *
	 * bpost occasionally answers with an HTML error page or a bare text/plain message, so callers
	 * decide what an unparsable body means rather than getting an exception raised at them here.
	 */
	public static function tryParse(string $body): ?Element
	{
		if (trim($body) === '')
		{
			return null;
		}

		try
		{
			$document = XMLDocument::createFromString($body, LIBXML_NOBLANKS | LIBXML_NOERROR);
		}
		catch (DOMException|ValueError)
		{
			return null;
		}

		return $document->documentElement;
	}

	/**
	 * The namespace a prefix is declared under. Services override this with their own map.
	 */
	protected static function namespaceFor(?string $prefix): ?string
	{
		return null;
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
