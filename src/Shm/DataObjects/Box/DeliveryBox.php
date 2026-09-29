<?php

namespace Webatvantage\Bpost\Api\Shm\DataObjects\Box;

use Webatvantage\Bpost\Api\Contracts\Option;
use Webatvantage\Bpost\Api\Contracts\XmlNamespace;
use Webatvantage\Bpost\Api\Contracts\XmlSerializable;
use Webatvantage\Bpost\Api\Exceptions\InvalidValueException;
use Webatvantage\Bpost\Api\Exceptions\UnexpectedValueException;
use Webatvantage\Bpost\Api\Shm\DataObjects\Options\OptionFactory;
use Webatvantage\Bpost\Api\Shm\Enums\Product;
use Webatvantage\Bpost\Api\Shm\Enums\ShmNamespace;
use Webatvantage\Bpost\Api\Support\Assert;
use Webatvantage\Bpost\Api\Support\XmlElement;

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

	/** The namespace this method's own elements are written in. */
	abstract protected function childNamespace(): XmlNamespace;

	abstract protected function buildElement(XmlElement $wrapper): XmlElement;

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
				allowed: array_map(fn (Product $product) => $product->value, static::allowedProducts()),
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

	public function toXml(XmlElement $parent, ?XmlNamespace $namespace = ShmNamespace::Global): XmlElement
	{
		$wrapper = $parent->appendElement($this->wrapperName(), $namespace);
		$this->buildElement($wrapper);

		return $wrapper;
	}

	/**
	 * Write the <options> element, unless the box carries none.
	 */
	protected function appendOptions(XmlElement $parent): void
	{
		if (count($this->options) === 0)
		{
			return;
		}

		$element = $parent->appendElement('options', $this->childNamespace());

		foreach ($this->options as $option)
		{
			$option->toXml($element, ShmNamespace::Common);
		}
	}

	/**
	 * The product named in a response, for handing to the constructor.
	 *
	 * An unrecognised name throws rather than being dropped: a box whose product we do not know is
	 * one we cannot send back, so failing here is more useful than failing later with less to go on.
	 *
	 * @throws InvalidValueException
	 */
	protected static function readProduct(XmlElement $xml): Product
	{
		$value = $xml->text('product') ?? '';
		$product = Product::tryFrom($value);

		if ($product === null)
		{
			throw new UnexpectedValueException(
				name: 'product',
				value: $value,
				known: array_map(fn (Product $product) => $product->value, static::allowedProducts()),
			);
		}

		return $product;
	}

	/**
	 * Read options and weight, which every delivery method shares.
	 */
	protected function readCommon(XmlElement $xml, string $weightElement = 'weight'): void
	{
		$weight = $xml->text($weightElement);

		if ($weight !== null)
		{
			$this->weight = (int)$weight;
		}

		$options = $xml->child('options');

		if ($options === null)
		{
			return;
		}

		foreach ($options->childElements() as $option)
		{
			$this->withOption(OptionFactory::fromXml($option));
		}
	}
}
