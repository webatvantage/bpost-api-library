<?php

namespace Webatvantage\Bpost\Api\Shm\DataObjects\Box;

use DOMDocument;
use DOMElement;
use SimpleXMLElement;
use Webatvantage\Bpost\Api\Contracts\XmlSerializable;
use Webatvantage\Bpost\Api\Exceptions\InvalidValueException;
use Webatvantage\Bpost\Api\Shm\DataObjects\Option\Option;
use Webatvantage\Bpost\Api\Shm\DataObjects\Option\OptionFactory;
use Webatvantage\Bpost\Api\Shm\Enums\Product;
use Webatvantage\Bpost\Api\Shm\Support\Assert;
use Webatvantage\Bpost\Api\Shm\Support\Xml;

/**
 * A delivery method: where and how one box is delivered.
 *
 * Each subclass writes its own wrapper, because bpost nests the choice twice — a nationalBox or an
 * internationalBox, and inside that the method itself.
 */
abstract class DeliveryBox implements XmlSerializable
{
	/** bpost rejects anything over 30 kg outright. */
	public const MAX_WEIGHT = 30000;

	public private(set) ?Product $product = null;

	/** @var array<int, Option> */
	public private(set) array $options = [];

	public private(set) ?int $weight = null;

	/**
	 * The products bpost accepts for this delivery method.
	 *
	 * @return array<int, Product>
	 */
	abstract public static function allowedProducts(): array;

	/** nationalBox or internationalBox. */
	abstract protected function wrapperName(): string;

	/** atHome, atBpost, at24-7, international or atIntlPugo. */
	abstract protected function elementName(): string;

	/** The prefix this method's own elements take; null means the default namespace. */
	abstract protected function childPrefix(): ?string;

	abstract protected function buildElement(DOMDocument $document): DOMElement;

	/**
	 * @throws InvalidValueException
	 */
	public function product(Product $product): static
	{
		if (!in_array($product, static::allowedProducts(), true))
		{
			throw new InvalidValueException(
				'product',
				$product->value,
				array_column(array_map(fn (Product $p) => ['v' => $p->value], static::allowedProducts()), 'v'),
			);
		}

		$this->product = $product;

		return $this;
	}

	/**
	 * @param int $weight In grams
	 *
	 * @throws InvalidValueException
	 */
	public function weight(int $weight): static
	{
		$this->weight = Assert::between('weight', $weight, 0, self::MAX_WEIGHT);

		return $this;
	}

	public function withOption(Option $option): static
	{
		$this->options[] = $option;

		return $this;
	}

	public function withOptions(Option ...$options): static
	{
		foreach ($options as $option)
		{
			$this->withOption($option);
		}

		return $this;
	}

	public function toXml(DOMDocument $document, ?string $prefix = Xml::PREFIX_GLOBAL): DOMElement
	{
		$wrapper = $document->createElement(Xml::prefixed($this->wrapperName(), $prefix));
		$wrapper->appendChild($this->buildElement($document));

		return $wrapper;
	}

	/**
	 * The <options> element, or null when the box carries none.
	 */
	protected function buildOptions(DOMDocument $document): ?DOMElement
	{
		if (count($this->options) === 0)
		{
			return null;
		}

		$element = $document->createElement(Xml::prefixed('options', $this->childPrefix()));

		foreach ($this->options as $option)
		{
			$element->appendChild($option->toXml($document));
		}

		return $element;
	}

	/**
	 * Read product, options and weight, which every delivery method shares.
	 */
	protected function readCommon(SimpleXMLElement $xml, string $weightElement = 'weight'): void
	{
		if (isset($xml->product))
		{
			$this->product = Product::tryFrom(trim((string)$xml->product));
		}

		if (isset($xml->{$weightElement}) && trim((string)$xml->{$weightElement}) !== '')
		{
			$this->weight((int)$xml->{$weightElement});
		}

		if (!isset($xml->options))
		{
			return;
		}

		foreach (Xml::readChildren($xml->options, Xml::READ_COMMON) as $option)
		{
			$this->withOption(OptionFactory::fromXml($option));
		}
	}
}
