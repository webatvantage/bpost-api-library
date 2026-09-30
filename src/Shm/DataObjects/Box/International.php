<?php

namespace Webatvantage\Bpost\Api\Shm\DataObjects\Box;

use Webatvantage\Bpost\Api\Contracts\XmlDeserializable;
use Webatvantage\Bpost\Api\Exceptions\InvalidArgumentException;
use Webatvantage\Bpost\Api\Exceptions\InvalidValueException;
use Webatvantage\Bpost\Api\Exceptions\UnexpectedValueException;
use Webatvantage\Bpost\Api\Shm\DataObjects\Customs\ParcelContent;
use Webatvantage\Bpost\Api\Shm\Enums\Product;
use Webatvantage\Bpost\Api\Support\XmlElement;

/**
 * Delivery to an address abroad.
 */
class International extends InternationalBox implements XmlDeserializable
{
	/** bpost accepts between one and ten contents. */
	public const int MAX_PARCEL_CONTENTS = 10;

	/** @var array<int, ParcelContent> */
	public private(set) array $parcelContents = [];

	/**
	 * @throws InvalidValueException
	 */
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

	/**
	 * @throws InvalidValueException
	 */
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

	/**
	 * @throws InvalidArgumentException
	 */
	protected function buildElement(XmlElement $wrapper): XmlElement
	{
		$namespace = $this->childNamespace();
		$element = $wrapper->appendElement($this->elementName(), $namespace);

		$this->appendShared($element);

		if (count($this->parcelContents) === 0)
		{
			return $element;
		}

		$contents = $element->appendElement('parcelContents', $namespace);

		foreach ($this->parcelContents as $content)
		{
			$content->toXml($contents, $namespace);
		}

		return $element;
	}

	/**
	 * @throws InvalidValueException
	 * @throws UnexpectedValueException
	 */
	public static function fromXml(XmlElement $xml): static
	{
		$box = new static(self::readProduct($xml));
		$box->readShared($xml);

		$contents = $xml->child('parcelContents');

		if ($contents === null)
		{
			return $box;
		}

		foreach ($contents->childElements() as $content)
		{
			$box->parcelContents[] = ParcelContent::fromXml($content);
		}

		return $box;
	}
}
