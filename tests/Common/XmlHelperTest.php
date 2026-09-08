<?php

namespace Bpost\BpostApiClient\Tests\Common;

use Bpost\BpostApiClient\Common\XmlHelper;
use DOMDocument;
use PHPUnit\Framework\TestCase;

class XmlHelperTest extends TestCase
{
    public function testGetPrefixedTagName()
    {
        $this->assertSame('text', XmlHelper::getPrefixedTagName('text'));
        $this->assertSame('text', XmlHelper::getPrefixedTagName('text', null));
        $this->assertSame('tns:text', XmlHelper::getPrefixedTagName('text', 'tns'));
    }

    public function testCreateTextElement()
    {
        $document = new DOMDocument('1.0', 'utf-8');
        $document->appendChild(XmlHelper::createTextElement($document, 'text', 'just a random text'));

        $this->assertSame('<text>just a random text</text>', trim($document->saveXML($document->documentElement)));
    }

    /**
     * The value may not be parsed as XML, or characters like '&' and '<' would corrupt the
     * element content
     */
    public function testCreateTextElementEscapesTheValue()
    {
        $document = new DOMDocument('1.0', 'utf-8');
        $document->appendChild(XmlHelper::createTextElement($document, 'text', 'Lisa&jo sandalen <goud>'));

        $this->assertSame(
            '<text>Lisa&amp;jo sandalen &lt;goud&gt;</text>',
            trim($document->saveXML($document->documentElement))
        );

        $xml = simplexml_load_string($document->saveXML());
        $this->assertSame('Lisa&jo sandalen <goud>', (string) $xml);
    }

    public function testCreateTextElementCastsTheValueToAString()
    {
        $document = new DOMDocument('1.0', 'utf-8');
        $document->appendChild(XmlHelper::createTextElement($document, 'nbOfItems', 3));

        $this->assertSame('<nbOfItems>3</nbOfItems>', trim($document->saveXML($document->documentElement)));
    }
}
