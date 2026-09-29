<?php

namespace Webatvantage\Bpost\Api\Tests\Support;

use Webatvantage\Bpost\Api\Shm\Enums\ShmNamespace;
use Webatvantage\Bpost\Api\Support\XmlDocument;
use Webatvantage\Bpost\Api\Tests\TestCase;

class XmlElementTest extends TestCase
{
	private function root(): \Webatvantage\Bpost\Api\Support\XmlElement
	{
		return XmlDocument::create()->root('order', ShmNamespace::Global);
	}

	public function test_it_qualifies_a_tag_with_its_namespaces_prefix()
	{
		$this->assertSame('common:name', ShmNamespace::Common->qualify('name'));
		$this->assertSame('tns:order', ShmNamespace::Global->qualify('order'));

		// The national namespace is an order's default, so it takes no prefix.
		$this->assertSame('atHome', ShmNamespace::National->qualify('atHome'));
	}

	/**
	 * A receiver called "Dupont & Fils" is enough to make bpost reject the whole document.
	 */
	public function test_it_escapes_text_values()
	{
		$root = $this->root();
		$root->appendText('company', 'Dupont & Fils <BE>', ShmNamespace::Common);

		$this->assertStringContainsString('Dupont &amp; Fils &lt;BE&gt;', (string)$root->C14N());
	}

	public function test_it_writes_booleans_as_bpost_spells_them()
	{
		$root = $this->root();
		$root->appendText('privateAddress', false, ShmNamespace::National);

		$this->assertStringContainsString('<privateAddress>false</privateAddress>', (string)$root->C14N());
	}

	/**
	 * An empty element is not the same as an absent one; bpost's own example carries the comment
	 * "When box is empty this tag has to be removed".
	 */
	public function test_it_skips_a_value_that_is_null_or_empty()
	{
		$root = $this->root();
		$root->appendText('box', null, ShmNamespace::Common);
		$root->appendText('number', '', ShmNamespace::Common);
		$root->appendText('locality', 'Brussel', ShmNamespace::Common);

		$xml = (string)$root->C14N();

		$this->assertStringNotContainsString('box', $xml);
		$this->assertStringNotContainsString('number', $xml);
		$this->assertStringContainsString('Brussel', $xml);
	}

	public function test_it_parses_well_formed_xml()
	{
		$xml = XmlDocument::tryParse('<order><reference>abc</reference></order>');

		$this->assertNotNull($xml);
		$this->assertSame('order', $xml->localName);
		$this->assertSame('abc', $xml->text('reference'));
	}

	/**
	 * bpost's own example responses use prefixes they never declare, and the Geolocator spells the
	 * same field differently per operation, so a lookup matches on local name and takes every
	 * spelling the caller knows.
	 */
	public function test_it_reads_children_whatever_namespace_they_claim()
	{
		$xml = XmlDocument::tryParse(
			'<order xmlns:ns2="urn:undeclared-elsewhere"><ns2:reference> abc </ns2:reference>'
			. '<blank>  </blank><line>1</line><line>2</line></order>',
		);

		$this->assertNotNull($xml);
		$this->assertSame('abc', $xml->text('reference'));
		$this->assertSame('abc', $xml->text('Reference', 'reference'));
		$this->assertNotNull($xml->child('reference'));

		// Present but blank reads as absent, because bpost sends both to mean the same thing.
		$this->assertNull($xml->text('blank'));
		$this->assertNull($xml->text('missing'));
		$this->assertNull($xml->child('missing'));

		$this->assertCount(2, $xml->children('line'));
		$this->assertCount(4, $xml->childElements());
	}

	public function test_it_reads_attributes_outside_any_namespace_scope()
	{
		$xml = XmlDocument::tryParse('<additionalInsurance value="3" empty=" "/>');

		$this->assertNotNull($xml);
		$this->assertSame('3', $xml->attribute('value'));
		$this->assertSame(3, $xml->integerAttribute('value'));
		$this->assertNull($xml->attribute('empty'));
		$this->assertNull($xml->attribute('missing'));
		$this->assertNull($xml->integerAttribute('missing'));
	}

	public function test_it_returns_null_for_unparsable_bodies()
	{
		$this->assertNull(XmlDocument::tryParse(''));
		$this->assertNull(XmlDocument::tryParse('   '));
		$this->assertNull(XmlDocument::tryParse('<html><body>oops'));
		$this->assertNull(XmlDocument::tryParse('not xml at all'));
	}
}
