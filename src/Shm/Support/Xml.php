<?php

namespace Webatvantage\Bpost\Api\Shm\Support;

use Dom\Element;
use Webatvantage\Bpost\Api\Support\Xml as BaseXml;

/**
 * The Shipping Manager's XML namespaces, and the prefixes it expects each element under.
 *
 * bpost reads requests against the v5 schema but answers with the v3 namespaces — see CHANGELOG
 * 3.5.1, where this was observed against the live API rather than inferred, and why the two sets
 * deliberately disagree.
 *
 * Only WRITE_* and READ_GLOBAL are written; responses are matched on local name, because several
 * of bpost's own examples use prefixes they never declare. The remaining READ_* are published so
 * that a caller checking what came back against what went out can see the version gap.
 */
class Xml extends BaseXml
{
	public const string WRITE_GLOBAL = 'http://schema.post.be/shm/deepintegration/v5/';
	public const string WRITE_COMMON = 'http://schema.post.be/shm/deepintegration/v5/common';
	public const string WRITE_NATIONAL = 'http://schema.post.be/shm/deepintegration/v5/national';
	public const string WRITE_INTERNATIONAL = 'http://schema.post.be/shm/deepintegration/v5/international';

	public const string READ_GLOBAL = 'http://schema.post.be/shm/deepintegration/v3/';
	public const string READ_COMMON = 'http://schema.post.be/shm/deepintegration/v3/common';
	public const string READ_NATIONAL = 'http://schema.post.be/shm/deepintegration/v3/national';
	public const string READ_INTERNATIONAL = 'http://schema.post.be/shm/deepintegration/v3/international';

	/** Prefix for elements in the order namespace. */
	public const string PREFIX_GLOBAL = 'tns';

	/** Prefix for the international box and everything inside it. */
	public const string PREFIX_INTERNATIONAL = 'international';

	/**
	 * Declare every namespace on the root element, as bpost's own examples do.
	 *
	 * All four are declared even when a given order only uses two; the examples are consistent
	 * about it and the XSD validates against the full set.
	 */
	public static function declareNamespaces(Element $root): Element
	{
		$root->setAttributeNS(self::XMLNS, 'xmlns', self::WRITE_NATIONAL);
		$root->setAttributeNS(self::XMLNS, 'xmlns:' . self::PREFIX_COMMON, self::WRITE_COMMON);
		$root->setAttributeNS(self::XMLNS, 'xmlns:' . self::PREFIX_GLOBAL, self::WRITE_GLOBAL);
		$root->setAttributeNS(self::XMLNS, 'xmlns:' . self::PREFIX_INTERNATIONAL, self::WRITE_INTERNATIONAL);
		$root->setAttributeNS(self::XMLNS, 'xmlns:xsi', self::XSI);
		$root->setAttributeNS(self::XSI, 'xsi:schemaLocation', self::WRITE_GLOBAL);

		return $root;
	}

	/**
	 * The namespace each prefix is declared under inside an order document.
	 *
	 * The unprefixed default is the national namespace here. It is not in every Shipping Manager
	 * document — batchLabels and orderUpdate each default to a global namespace of their own — so
	 * those two build their elements with an explicit URI rather than through this map.
	 */
	protected static function namespaceFor(?string $prefix): ?string
	{
		return match ($prefix)
		{
			null, '' => self::WRITE_NATIONAL,
			self::PREFIX_COMMON => self::WRITE_COMMON,
			self::PREFIX_GLOBAL => self::WRITE_GLOBAL,
			self::PREFIX_INTERNATIONAL => self::WRITE_INTERNATIONAL,
			default => null,
		};
	}
}
