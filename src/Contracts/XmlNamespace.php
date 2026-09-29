<?php

namespace Webatvantage\Bpost\Api\Contracts;

interface XmlNamespace
{
	public function uri(): string;

	/**
	 * The prefix bpost qualifies this namespace with, or null where it is the document's default.
	 */
	public function prefix(): ?string;

	/**
	 * Qualify a tag name with this namespace's prefix, when it has one.
	 */
	public function qualify(string $tagName): string;
}
