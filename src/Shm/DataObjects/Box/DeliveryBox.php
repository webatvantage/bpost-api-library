<?php

namespace Webatvantage\Bpost\Api\Shm\DataObjects\Box;

use DOMDocument;
use DOMElement;
use SimpleXMLElement;
use Webatvantage\Bpost\Api\Contracts\Option;
use Webatvantage\Bpost\Api\Contracts\XmlSerializable;
use Webatvantage\Bpost\Api\Exceptions\InvalidValueException;
use Webatvantage\Bpost\Api\Exceptions\UnexpectedValueException;
use Webatvantage\Bpost\Api\Shm\DataObjects\Options\OptionFactory;
use Webatvantage\Bpost\Api\Shm\Enums\Product;
use Webatvantage\Bpost\Api\Shm\Support\Xml;
use Webatvantage\Bpost\Api\Support\Assert;

/**
 * A delivery method: where and how one box is delivered.
 *
 * Each subclass writes its own wrapper, because bpost nests the choice twice — a nationalBox or an
 * internationalBox, and inside that the method itself.
 */
abstract class DeliveryBox implements XmlSerializable
{
	/** The ceiling for an ordinary parcel. */
	public const int MAX_WEIGHT = Product::MAX_WEIGHT;

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
				name: 'product',
				value: $product->value,
				allowed: array_map(fn (Product $p) => $p->value, static::allowedProducts()),
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
		$max = $this->product === null ? self::MAX_WEIGHT : $this->product->maxWeight();

		$this->weight = $max === null
			? Assert::atLeast('weight', $weight, 0)
			: Assert::between('weight', $weight, 0, $max);

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
	 * The product named in a response, for handing to the constructor.
	 *
	 * An unrecognised name throws rather than being dropped: a box whose product we do not know is
	 * one we cannot send back, so failing here is more useful than failing later with less to go on.
	 *
	 * @throws InvalidValueException
	 */
	protected static function readProduct(SimpleXMLElement $xml): Product
	{
		$value = trim((string)($xml->product ?? ''));
		$product = Product::tryFrom($value);

		if ($product === null)
		{
			throw new UnexpectedValueException(
				name: 'product',
				value: $value,
				known: array_map(fn (Product $p) => $p->value, static::allowedProducts()),
			);
		}

		return $product;
	}

	/**
	 * Read options and weight, which every delivery method shares.
	 */
	protected function readCommon(SimpleXMLElement $xml, string $weightElement = 'weight'): void
	{
		if (isset($xml->{$weightElement}) && trim((string)$xml->{$weightElement}) !== '')
		{
			$this->weight = (int)$xml->{$weightElement};
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
