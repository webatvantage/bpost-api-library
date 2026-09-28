<?php

namespace Webatvantage\Bpost\Api\Tests\Shm;

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
		$document->appendChild($build($document, $prefix));

		return trim(Xml::toString($document));
	}

	/**
	 * Compare a generated fragment against expected markup.
	 *
	 * The comparison is on whitespace-normalised text rather than a reparsed DOM: a fragment uses
	 * the common: and tns: prefixes without declaring them, so reloading it raises namespace
	 * warnings for markup that is correct in the document it will be appended to.
	 */
	protected function assertXmlFragment(string $expected, string $actual): void
	{
		$this->assertSame($this->normalise($expected), $this->normalise($actual));
	}

	private function normalise(string $xml): string
	{
		$xml = preg_replace('/<\?xml[^>]*\?>/', '', $xml) ?? $xml;
		$xml = preg_replace('/>\s+</', '><', $xml) ?? $xml;

		return trim($xml);
	}
}
