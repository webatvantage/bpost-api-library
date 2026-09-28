<?php

namespace Webatvantage\Bpost\Api\Parcel\Support;

use DOMElement;
use Webatvantage\Bpost\Api\Support\Xml as BaseXml;

/**
 * The namespaces the announcement and tracking services use.
 *
 * They share one common namespace for addresses and parties, so a tracking response describes a
 * sender with the very elements an announcement request used to declare it.
 */
class Xml extends BaseXml
{
	public const string ANNOUNCEMENT = 'http://schema.post.be/announcement/v1/';
	public const string COMMON = 'http://schema.post.be/announcement/common/v1/';
	public const string TRACKING = 'http://schema.post.be/tracking/v1/';

	/** Prefix for the announcement's own elements. */
	public const string PREFIX_ANNOUNCEMENT = 'inst';

	public static function declareNamespaces(DOMElement $root): DOMElement
	{
		$root->setAttribute('xmlns:' . self::PREFIX_COMMON, self::COMMON);
		$root->setAttribute('xmlns:' . self::PREFIX_ANNOUNCEMENT, self::ANNOUNCEMENT);
		$root->setAttribute('xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');

		return $root;
	}
}
