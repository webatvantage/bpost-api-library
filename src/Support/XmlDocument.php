<?php

namespace Webatvantage\Bpost\Api\Support;

use Dom\Element;
use Dom\XMLDocument as DomDocument;
use DOMException;
use ValueError;
use Webatvantage\Bpost\Api\Contracts\XmlNamespace;

/**
 * A bpost XML document, on the way out or on the way back.
 */
class XmlDocument
{
	/** Namespace declarations are attributes in this namespace, not plain ones. */
	public const string XMLNS = 'http://www.w3.org/2000/xmlns/';

	public const string XSI = 'http://www.w3.org/2001/XMLSchema-instance';

	private function __construct(private readonly DomDocument $document) {}

	public static function create(): static
	{
		$document = DomDocument::createEmpty(version: '1.0', encoding: 'UTF-8');
		$document->formatOutput = true;
		$document->registerNodeClass(Element::class, XmlElement::class);

		return new static($document);
	}

	/**
	 * Parse a response body, returning null when it is not well-formed XML.
	 *
	 * bpost occasionally answers with an HTML error page or a bare text/plain message, so callers
	 * decide what an unparsable body means rather than getting an exception raised at them here.
	 *
	 * The root element is what comes back, since that is what every caller reads from; it keeps
	 * the document it belongs to alive on its own.
	 */
	public static function tryParse(string $body): ?XmlElement
	{
		if ($body === '')
		{
			return null;
		}

		try
		{
			$document = DomDocument::createFromString($body, LIBXML_NOBLANKS | LIBXML_NOERROR);
		}
		catch (DOMException|ValueError)
		{
			return null;
		}

		$document->registerNodeClass(Element::class, XmlElement::class);
		$root = $document->documentElement;

		return $root instanceof XmlElement ? $root : null;
	}

	/**
	 * Create the document element and append it.
	 *
	 * The namespaces themselves are declared on it separately, by the service that owns them.
	 *
	 * @throws DOMException
	 */
	public function root(string $tagName, XmlNamespace $namespace): XmlElement
	{
		$root = $this->document->createElementNS($namespace->uri(), $namespace->qualify($tagName));
		$this->document->append($root);

		return $root instanceof XmlElement ? $root : throw new DOMException('The root was not an XmlElement.');
	}

	public function toString(): string
	{
		return (string)$this->document->saveXml();
	}
}
