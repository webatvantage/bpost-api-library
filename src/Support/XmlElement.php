<?php

namespace Webatvantage\Bpost\Api\Support;

use Dom\Element;
use Webatvantage\Bpost\Api\Contracts\XmlNamespace;
use Webatvantage\Bpost\Api\Exceptions\InvalidArgumentException;

/**
 * The element every bpost document is built from and read through.
 */
class XmlElement extends Element
{
	/**
	 * Create a child element in a namespace and append it.
	 *
	 * @throws InvalidArgumentException
	 */
	public function appendElement(string $tagName, ?XmlNamespace $namespace = null): static
	{
		$element = $this->ownerDocument?->createElementNS(
			namespace: $namespace?->uri(),
			qualifiedName: $namespace === null ? $tagName : $namespace->qualify($tagName),
		);

		if (!$element instanceof static)
		{
			throw new InvalidArgumentException('This element does not belong to a document built by XmlDocument.');
		}

		$this->append($element);

		return $element;
	}

	/**
	 * Append a text element, skipping it when the value is null or empty
	 */
	public function appendText(
		string $tagName,
		string|int|float|bool|null $value,
		?XmlNamespace $namespace = null,
	): void {
		if ($value === null || $value === '')
		{
			return;
		}

		$element = $this->appendElement($tagName, $namespace);

		$element->textContent = self::stringify($value);
	}

	/**
	 * The first child element with one of these local names, or null.
	 */
	public function child(string ...$localNames): ?self
	{
		foreach ($localNames as $localName)
		{
			foreach ($this->children as $child)
			{
				if ($child instanceof self && $child->localName === $localName)
				{
					return $child;
				}
			}
		}

		return null;
	}

	/**
	 * Every child element with this local name, in document order.
	 *
	 * @return array<self>
	 */
	public function children(string $localName): array
	{
		$found = [];

		foreach ($this->children as $child)
		{
			if ($child instanceof self && $child->localName === $localName)
			{
				$found[] = $child;
			}
		}

		return $found;
	}

	/**
	 * Every child element, whatever it is called.
	 *
	 * @return array<self>
	 */
	public function childElements(): array
	{
		$found = [];

		foreach ($this->children as $child)
		{
			if ($child instanceof self)
			{
				$found[] = $child;
			}
		}

		return $found;
	}

	/**
	 * The trimmed text of the first child element with one of these local names.
	 */
	public function text(string ...$localNames): ?string
	{
		$child = $this->child(...$localNames);

		if ($child === null)
		{
			return null;
		}

		$value = trim($child->textContent);

		return $value === '' ? null : $value;
	}

	public function attribute(string $name): ?string
	{
		$value = $this->getAttribute($name);

		return $value === null || trim($value) === '' ? null : trim($value);
	}

	public function integerAttribute(string $name): ?int
	{
		$value = $this->attribute($name);

		return $value === null ? null : (int)$value;
	}

	private static function stringify(string|int|float|bool $value): string
	{
		if (is_bool($value))
		{
			return $value ? 'true' : 'false';
		}

		return (string)$value;
	}
}
