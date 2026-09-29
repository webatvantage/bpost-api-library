<?php

namespace Webatvantage\Bpost\Api\Parcel\Enums;

use Webatvantage\Bpost\Api\Contracts\XmlNamespace;
use Webatvantage\Bpost\Api\Support\XmlDocument;
use Webatvantage\Bpost\Api\Support\XmlElement;

/**
 * The parcel announcement and tracking namespaces.
 */
enum ParcelNamespace: string implements XmlNamespace
{
	case Announcement = 'http://schema.post.be/announcement/v1/';
	case Common = 'http://schema.post.be/announcement/common/v1/';
	case Tracking = 'http://schema.post.be/tracking/v1/';

	public function uri(): string
	{
		return $this->value;
	}

	public function prefix(): ?string
	{
		return match ($this)
		{
			self::Announcement => 'inst',
			self::Common => 'common',
			self::Tracking => null,
		};
	}

	public function qualify(string $tagName): string
	{
		$namespace = $this->prefix();

		return $namespace === null ? $tagName : $namespace . ':' . $tagName;
	}

	public static function declareOn(XmlElement $root): void
	{
		foreach ([self::Common, self::Announcement] as $namespace)
		{
			$root->setAttributeNS(XmlDocument::XMLNS, 'xmlns:' . $namespace->prefix(), $namespace->uri());
		}

		$root->setAttributeNS(XmlDocument::XMLNS, 'xmlns:xsi', XmlDocument::XSI);
	}
}
