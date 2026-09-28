<?php

namespace Webatvantage\Bpost\Api\Tests\Support;

use Webatvantage\Bpost\Api\Support\Xml;
use Webatvantage\Bpost\Api\Tests\TestCase;

class XmlTest extends TestCase
{
	public function test_it_prefixes_a_tag_name()
	{
		$this->assertSame('common:name', Xml::prefixed('name', 'common'));
		$this->assertSame('name', Xml::prefixed('name'));
		$this->assertSame('name', Xml::prefixed('name', ''));
	}

	/**
	 * A receiver called "Dupont & Fils" is enough to make bpost reject the whole document.
	 */
	public function test_it_escapes_text_values()
	{
		$document = Xml::document();
		$document->appendChild(Xml::createTextElement($document, 'company', 'Dupont & Fils <BE>'));

		$this->assertStringContainsString('Dupont &amp; Fils &lt;BE&gt;', Xml::toString($document));
	}

	public function test_it_writes_booleans_as_bpost_spells_them()
	{
		$document = Xml::document();
		$document->appendChild(Xml::createTextElement($document, 'privateAddress', false));

		$this->assertStringContainsString('<privateAddress>false</privateAddress>', Xml::toString($document));
	}

	public function test_it_parses_well_formed_xml()
	{
		$xml = Xml::tryParse('<order><reference>abc</reference></order>');

		$this->assertNotNull($xml);
		$this->assertSame('abc', (string)$xml->reference);
	}

	public function test_it_returns_null_for_unparsable_bodies()
	{
		$this->assertNull(Xml::tryParse(''));
		$this->assertNull(Xml::tryParse('   '));
		$this->assertNull(Xml::tryParse('<html><body>oops'));
	}
}
