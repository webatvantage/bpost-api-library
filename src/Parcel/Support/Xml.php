<?php

namespace Webatvantage\Bpost\Api\Parcel\Support;

use Dom\Element;
use Webatvantage\Bpost\Api\Support\Xml as BaseXml;

/**
 * The parcel announcement and tracking namespaces.
 *
 * TRACKING is published rather than used: tracking responses are matched on local name, as bpost
 * binds the parties in them to the announcement service's common namespace instead of its own.
 */
class Xml extends BaseXml
{
	public const string ANNOUNCEMENT = 'http://schema.post.be/announcement/v1/';
	public const string COMMON = 'http://schema.post.be/announcement/common/v1/';
	public const string TRACKING = 'http://schema.post.be/tracking/v1/';

	public const string PREFIX_ANNOUNCEMENT = 'inst';

	public static function declareNamespaces(Element $root): Element
	{
		$root->setAttributeNS(self::XMLNS, 'xmlns:' . self::PREFIX_COMMON, self::COMMON);
		$root->setAttributeNS(self::XMLNS, 'xmlns:' . self::PREFIX_ANNOUNCEMENT, self::ANNOUNCEMENT);
		$root->setAttributeNS(self::XMLNS, 'xmlns:xsi', self::XSI);

		return $root;
	}

	/**
	 * An announcement declares no default namespace, so an unprefixed element has none.
	 */
	protected static function namespaceFor(?string $prefix): ?string
	{
		return match ($prefix)
		{
			self::PREFIX_COMMON => self::COMMON,
			self::PREFIX_ANNOUNCEMENT => self::ANNOUNCEMENT,
			default => null,
		};
	}
}
