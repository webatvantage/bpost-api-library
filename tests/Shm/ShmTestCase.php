<?php

namespace Webatvantage\Bpost\Api\Tests\Shm;

use Dom\XMLDocument;
use DOMException;
use Webatvantage\Bpost\Api\Shm\ShmApiConfig;
use Webatvantage\Bpost\Api\Shm\Support\Xml;
use Webatvantage\Bpost\Api\Tests\TestCase;

abstract class ShmTestCase extends TestCase
{
	protected function config(): ShmApiConfig
	{
		return new ShmApiConfig(accountId: '123456', passphrase: 'passphrase');
	}

	/**
	 * Serialise a fragment on its own, so a test can assert the element shape without an order
	 * wrapped around it.
	 */
	protected function serialise(callable $build, ?string $prefix = null): string
	{
		$document = Xml::document();

		// Only pass a prefix when the test names one, so each class keeps its own default.
		$document->append($prefix === null ? $build($document) : $build($document, $prefix));

		return trim(Xml::toString($document));
	}

	protected function fixture(string $name): string
	{
		$contents = file_get_contents(__DIR__ . '/../Fixtures/Shm/' . $name);

		if ($contents === false)
		{
			throw new \RuntimeException("Fixture not found: {$name}");
		}

		return $contents;
	}

	/**
	 * Compare generated markup against what bpost documents, in canonical form.
	 *
	 * Both sides go into the same declaring wrapper before being canonicalised. A fragment written
	 * on its own carries the declarations it needs, while the expected markup in a test names the
	 * common: and tns: prefixes without declaring them; wrapping both makes the pair comparable and
	 * lets C14N drop the redeclaration as redundant. Canonicalising also settles attribute order,
	 * indentation and bpost's inline comments, which are guidance rather than contract.
	 */
	protected function assertXmlFragment(string $expected, string $actual): void
	{
		$this->assertSame($this->canonicalise($expected), $this->canonicalise($actual));
	}

	/**
	 * Assert that one element appears in generated markup, without pinning the rest of it.
	 *
	 * The generated side is canonicalised so it carries no namespace declarations of its own — a
	 * fragment serialised alone has to repeat them, a fragment inside the document it belongs to
	 * does not. The expectation is canonicalised too when it stands on its own; an opening tag such
	 * as `<at24-7>` cannot be, and is matched against the canonical text as written.
	 */
	protected function assertXmlContains(string $expected, string $actual): void
	{
		try
		{
			$expected = $this->canonicalise($expected);
		}
		catch (DOMException)
		{
			$expected = trim($expected);
		}

		$this->assertStringContainsString($expected, $this->canonicalise($actual));
	}

	private function canonicalise(string $xml): string
	{
		// A declaration is only legal at the top of a document, not inside the wrapper.
		$xml = preg_replace('/<\?xml[^>]*\?>/', '', $xml) ?? $xml;

		$wrapped = sprintf(
			'<fragment xmlns="%s" xmlns:common="%s" xmlns:%s="%s" xmlns:%s="%s" xmlns:xsi="%s">%s</fragment>',
			Xml::WRITE_NATIONAL,
			Xml::WRITE_COMMON,
			Xml::PREFIX_GLOBAL,
			Xml::WRITE_GLOBAL,
			Xml::PREFIX_INTERNATIONAL,
			Xml::WRITE_INTERNATIONAL,
			Xml::XSI,
			trim($xml),
		);

		$document = XMLDocument::createFromString($wrapped, LIBXML_NOBLANKS | LIBXML_NOERROR);
		$canonical = (string)$document->documentElement?->C14N();

		// Drop the wrapper itself, so what is compared is only what the caller wrote.
		$start = strpos($canonical, '>');
		$end = strrpos($canonical, '</fragment>');

		return $start === false || $end === false
			? $canonical
			: substr($canonical, $start + 1, $end - $start - 1);
	}
}
