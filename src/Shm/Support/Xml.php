<?php

namespace Webatvantage\Bpost\Api\Shm\Support;

use DOMDocument;
use DOMElement;
use SimpleXMLElement;
use Webatvantage\Bpost\Api\Support\Xml as BaseXml;

/**
 * The Shipping Manager's XML namespaces, and the prefixes it expects each element under.
 *
 * bpost reads requests against the v5 schema but answers with the v3 namespaces — see CHANGELOG
 * 3.5.1, where this was observed against the live API rather than inferred. So documents are
 * written with WRITE_* and read with READ_*, and the two deliberately disagree.
 */
class Xml extends BaseXml
{
	public const WRITE_GLOBAL = 'http://schema.post.be/shm/deepintegration/v5/';
	public const WRITE_COMMON = 'http://schema.post.be/shm/deepintegration/v5/common';
	public const WRITE_NATIONAL = 'http://schema.post.be/shm/deepintegration/v5/national';
	public const WRITE_INTERNATIONAL = 'http://schema.post.be/shm/deepintegration/v5/international';

	public const READ_GLOBAL = 'http://schema.post.be/shm/deepintegration/v3/';
	public const READ_COMMON = 'http://schema.post.be/shm/deepintegration/v3/common';
	public const READ_NATIONAL = 'http://schema.post.be/shm/deepintegration/v3/national';
	public const READ_INTERNATIONAL = 'http://schema.post.be/shm/deepintegration/v3/international';

	/** Prefix for elements in the order namespace. */
	public const PREFIX_GLOBAL = 'tns';

	/** Prefix for addresses, names and options, which are shared between national and international. */
	public const PREFIX_COMMON = 'common';

	/** Prefix for the international box and everything inside it. */
	public const PREFIX_INTERNATIONAL = 'international';

	/**
	 * Declare every namespace on the root element, as bpost's own examples do.
	 *
	 * All four are declared even when a given order only uses two; the examples are consistent
	 * about it and the XSD validates against the full set.
	 */
	public static function declareNamespaces(DOMElement $root): DOMElement
	{
		$root->setAttribute('xmlns', self::WRITE_NATIONAL);
		$root->setAttribute('xmlns:' . self::PREFIX_COMMON, self::WRITE_COMMON);
		$root->setAttribute('xmlns:' . self::PREFIX_GLOBAL, self::WRITE_GLOBAL);
		$root->setAttribute('xmlns:' . self::PREFIX_INTERNATIONAL, self::WRITE_INTERNATIONAL);
		$root->setAttribute('xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');
		$root->setAttribute('xsi:schemaLocation', self::WRITE_GLOBAL);

		return $root;
	}

	/**
	 * Append a text element under a prefix, skipping it when the value is null.
	 *
	 * Most bpost elements are optional and an empty one is not the same as an absent one, so this
	 * guard is repeated often enough to be worth centralising.
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
	 * The children of an element, preferring a response namespace but not insisting on it.
	 *
	 * bpost's Retrieve Order Information responses carry ns2: and ns3: prefixes without declaring
	 * them, so they are not well-formed namespace-wise and a namespaced lookup finds nothing in
	 * them. Falling back to the default children keeps those responses parseable.
	 */
	public static function readChildren(SimpleXMLElement $xml, string $namespace): SimpleXMLElement
	{
		$children = $xml->children($namespace);

		return count($children) > 0 ? $children : $xml->children();
	}
}
