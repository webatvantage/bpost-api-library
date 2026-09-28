<?php

namespace Webatvantage\Bpost\Api\Shm\DataObjects\Box;

use DOMDocument;
use DOMElement;
use SimpleXMLElement;
use Webatvantage\Bpost\Api\Contracts\XmlDeserializable;
use Webatvantage\Bpost\Api\Exceptions\InvalidValueException;
use Webatvantage\Bpost\Api\Shm\DataObjects\Customs\ParcelContent;
use Webatvantage\Bpost\Api\Shm\Enums\Product;
use Webatvantage\Bpost\Api\Shm\Support\Xml;

/**
 * Delivery to an address abroad.
 */
class International extends InternationalBox implements XmlDeserializable
{
	/** bpost accepts between one and ten contents. */
	public const int MAX_PARCEL_CONTENTS = 10;

	/** @var array<int, ParcelContent> */
	public private(set) array $parcelContents = [];

	public function __construct(Product $product)
	{
		$this->product($product);
	}

	public static function allowedProducts(): array
	{
		return [
			Product::BpackWorldBusiness,
			Product::BpackWorldExpressPro,
			Product::BpackEuropeBusiness,
			Product::BpackWorldEasyReturn,
		];
	}

	/**
	 * Mandatory for anywhere outside the EU customs zone.
	 *
	 * @throws InvalidValueException
	 */
	public function withParcelContent(ParcelContent $content): static
	{
		if (count($this->parcelContents) >= self::MAX_PARCEL_CONTENTS)
		{
			throw new InvalidValueException(
				name: 'parcelContents',
				value: count($this->parcelContents) + 1,
				allowed: [sprintf('1 to %d', self::MAX_PARCEL_CONTENTS)],
			);
		}

		$this->parcelContents[] = $content;

		return $this;
	}

	public function withParcelContents(ParcelContent ...$contents): static
	{
		foreach ($contents as $content)
		{
			$this->withParcelContent($content);
		}

		return $this;
	}

	protected function elementName(): string
	{
		return 'international';
	}

	protected function buildElement(DOMDocument $document): DOMElement
	{
		$prefix = $this->childPrefix();
		$element = $document->createElement(Xml::prefixed($this->elementName(), $prefix));

		$this->appendShared($document, $element);

		if (count($this->parcelContents) === 0)
		{
			return $element;
		}

		$contents = $document->createElement(Xml::prefixed('parcelContents', $prefix));

		foreach ($this->parcelContents as $content)
		{
			$contents->appendChild($content->toXml($document, $prefix));
		}

		$element->appendChild($contents);

		return $element;
	}

	public static function fromXml(SimpleXMLElement $xml): static
	{
		$box = new static(self::readProduct($xml));
		$box->readShared($xml);

		if (!isset($xml->parcelContents))
		{
			return $box;
		}

		foreach (Xml::readChildren($xml->parcelContents, Xml::READ_INTERNATIONAL) as $content)
		{
			$box->withParcelContent(ParcelContent::fromXml($content));
		}

		return $box;
	}
}
