<?php

namespace Webatvantage\Bpost\Api\Support;

use Webatvantage\Bpost\Api\Contracts\XmlNamespace;

/**
 * A namespace written without a prefix, as a document's default
 */
final class DefaultNamespace implements XmlNamespace
{
	public function __construct(private readonly string $uri) {}

	public function uri(): string
	{
		return $this->uri;
	}

	public function prefix(): ?string
	{
		return null;
	}

	public function qualify(string $tagName): string
	{
		return $tagName;
	}
}
