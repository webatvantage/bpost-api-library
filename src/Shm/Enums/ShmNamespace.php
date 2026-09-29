<?php

namespace Webatvantage\Bpost\Api\Shm\Enums;

use Webatvantage\Bpost\Api\Contracts\XmlNamespace;
use Webatvantage\Bpost\Api\Support\XmlDocument;
use Webatvantage\Bpost\Api\Support\XmlElement;

/**
 * The Shipping Manager's XML namespaces, and the prefix it expects each one under.
 *
 * bpost reads requests against the v5 schema but answers with the v3 namespaces — see CHANGELOG
 * 3.5.1, where this was observed against the live API rather than inferred. Responses are matched
 * on local name, so the v3 namespaces are not needed to read one; the only v3 document the library
 * writes is the order update payload, which bpost never moved.
 *
 * A prefix belongs to the document shape rather than to the namespace. These are the ones an order
 * uses; batchLabels and orderUpdate put the global namespace in the default position instead, and
 * build their handful of elements with an explicit URI rather than through here.
 */
enum ShmNamespace: string implements XmlNamespace
{
	case Global = 'http://schema.post.be/shm/deepintegration/v5/';
	case Common = 'http://schema.post.be/shm/deepintegration/v5/common';
	case National = 'http://schema.post.be/shm/deepintegration/v5/national';
	case International = 'http://schema.post.be/shm/deepintegration/v5/international';

	/** The order update payload, which bpost still reads on v3. */
	case LegacyGlobal = 'http://schema.post.be/shm/deepintegration/v3/';

	public function uri(): string
	{
		return $this->value;
	}

	public function prefix(): ?string
	{
		return match ($this)
		{
			self::Global => 'tns',
			self::Common => 'common',
			self::International => 'international',
			self::National, self::LegacyGlobal => null,
		};
	}

	public function qualify(string $tagName): string
	{
		$namespace = $this->prefix();

		return $namespace === null ? $tagName : $namespace . ':' . $tagName;
	}

	/**
	 * Declare every namespace on the root element, as bpost's own examples do.
	 *
	 * All four are declared even when a given order only uses two; the examples are consistent
	 * about it and the XSD validates against the full set. The order they are written in is bpost's
	 * own, and a generated root is byte-identical to its examples because of it.
	 */
	public static function declareOn(XmlElement $root): void
	{
		$root->setAttributeNS(XmlDocument::XMLNS, 'xmlns', self::National->uri());

		foreach ([self::Common, self::Global, self::International] as $namespace)
		{
			$root->setAttributeNS(XmlDocument::XMLNS, 'xmlns:' . $namespace->prefix(), $namespace->uri());
		}

		$root->setAttributeNS(XmlDocument::XMLNS, 'xmlns:xsi', XmlDocument::XSI);
		$root->setAttributeNS(XmlDocument::XSI, 'xsi:schemaLocation', self::Global->uri());
	}
}
