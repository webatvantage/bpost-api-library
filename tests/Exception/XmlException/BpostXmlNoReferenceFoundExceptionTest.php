<?php

namespace Tests\Exception\XmlException;

use Bpost\BpostApiClient\Exception\XmlException\BpostXmlNoReferenceFoundException;
use PHPUnit\Framework\TestCase;

class BpostXmlNoReferenceFoundExceptionTest extends TestCase
{
    public function testGetMessage()
    {
        $ex = new BpostXmlNoReferenceFoundException();
        $this->assertSame('No reference found', $ex->getMessage());

        $ex = new BpostXmlNoReferenceFoundException('Oops');
        $this->assertSame('No reference found: Oops', $ex->getMessage());
    }
}
