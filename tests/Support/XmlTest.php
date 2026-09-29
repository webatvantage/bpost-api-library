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
		$document->append(Xml::createTextElement($document, 'company', 'Dupont & Fils <BE>'));

		$this->assertStringContainsString('Dupont &amp; Fils &lt;BE&gt;', Xml::toString($document));
	}

	public function test_it_writes_booleans_as_bpost_spells_them()
	{
		$document = Xml::document();
		$document->append(Xml::createTextElement($document, 'privateAddress', false));

		$this->assertStringContainsString('<privateAddress>false</privateAddress>', Xml::toString($document));
	}

	public function test_it_parses_well_formed_xml()
	{
		$xml = Xml::tryParse('<order><reference>abc</reference></order>');

		$this->assertNotNull($xml);
		$this->assertSame('order', $xml->localName);
		$this->assertSame('abc', Xml::text($xml, 'reference'));
	}

	/**
	 * bpost's own example responses use prefixes they never declare, and the Geolocator spells the
	 * same field differently per operation, so a lookup matches on local name and takes every
	 * spelling the caller knows.
	 */
	public function test_it_reads_children_whatever_namespace_they_claim()
	{
		$xml = Xml::tryParse(
			'<order xmlns:ns2="urn:undeclared-elsewhere"><ns2:reference> abc </ns2:reference>'
			. '<blank>  </blank><line>1</line><line>2</line></order>',
		);

		$this->assertNotNull($xml);
		$this->assertSame('abc', Xml::text($xml, 'reference'));
		$this->assertSame('abc', Xml::text($xml, 'Reference', 'reference'));
		$this->assertNotNull(Xml::child($xml, 'reference'));

		// Present but blank reads as absent, because bpost sends both to mean the same thing.
		$this->assertNull(Xml::text($xml, 'blank'));
		$this->assertNull(Xml::text($xml, 'missing'));
		$this->assertNull(Xml::child($xml, 'missing'));

		$this->assertCount(2, Xml::children($xml, 'line'));
	}

	public function test_it_reads_attributes_outside_any_namespace_scope()
	{
		$xml = Xml::tryParse('<additionalInsurance value="3" empty=" "/>');

		$this->assertNotNull($xml);
		$this->assertSame('3', Xml::attribute($xml, 'value'));
		$this->assertSame(3, Xml::integerAttribute($xml, 'value'));
		$this->assertNull(Xml::attribute($xml, 'empty'));
		$this->assertNull(Xml::attribute($xml, 'missing'));
		$this->assertNull(Xml::integerAttribute($xml, 'missing'));
	}

	/**
	 * A prefix the service does not map is a mistake in the library, not in bpost's answer.
	 */
	public function test_it_refuses_a_prefix_it_cannot_resolve()
	{
		$this->expectException(\DOMException::class);

		Xml::element(Xml::document(), 'name', 'nosuchprefix');
	}

	public function test_it_returns_null_for_unparsable_bodies()
	{
		$this->assertNull(Xml::tryParse(''));
		$this->assertNull(Xml::tryParse('   '));
		$this->assertNull(Xml::tryParse('<html><body>oops'));
		$this->assertNull(Xml::tryParse('not xml at all'));
	}
}
