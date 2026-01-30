<?php

namespace Bpost\BpostApiClient\Tests\Exception\XmlException;

use Bpost\BpostApiClient\Exception\XmlException\BpostXmlInvalidItemException;
use PHPUnit\Framework\TestCase;

class BpostXmlInvalidItemExceptionTest extends TestCase
{
    public function testGetMessage()
    {
        $ex = new BpostXmlInvalidItemException();
        $this->assertSame('Invalid item', $ex->getMessage());

        $ex = new BpostXmlInvalidItemException('Oops');
        $this->assertSame('Invalid item: Oops', $ex->getMessage());
    }
}
