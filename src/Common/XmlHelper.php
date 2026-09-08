<?php

namespace Bpost\BpostApiClient\Common;

use Bpost\BpostApiClient\Exception\BpostNotImplementedException;
use DOMDocument;
use DOMElement;

class XmlHelper
{
    /**
     * Prefix $tagName with the $prefix, if needed
     *
     * @param string $prefix
     * @param string $tagName
     *
     * @return string
     */
    public static function getPrefixedTagName($tagName, $prefix = null)
    {
        if (empty($prefix)) {
            return $tagName;
        }

        return $prefix . ':' . $tagName;
    }

    /**
     * Create an element holding a text value
     *
     * The value of DOMDocument::createElement() is parsed as XML, so an unescaped '&' or '<'
     * corrupts the content: depending on the libxml version the character is dropped or the
     * whole content is lost, after which bpost rejects the request on the field's pattern.
     * A text node escapes the value instead.
     *
     * @param DOMDocument $document
     * @param string      $tagName
     * @param string      $value
     *
     * @return DOMElement
     */
    public static function createTextElement(DOMDocument $document, $tagName, $value)
    {
        $element = $document->createElement($tagName);
        $element->appendChild($document->createTextNode((string) $value));

        return $element;
    }

    /**
     * @param string $className
     *
     * @throws BpostNotImplementedException
     */
    public static function assertMethodCreateFromXmlExists($className)
    {
        if (!method_exists($className, 'createFromXML')) {
            throw new BpostNotImplementedException('Method createFromXML not found for class ' . $className);
        }
    }
}
