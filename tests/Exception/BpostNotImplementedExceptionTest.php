<?php

namespace Bpost\BpostApiClient\Tests\Exception;

use Bpost\BpostApiClient\Exception\BpostNotImplementedException;
use PHPUnit\Framework\TestCase;

class BpostNotImplementedExceptionTest extends TestCase
{
    public function testGetMessage()
    {
        $ex = new BpostNotImplementedException();
        $this->assertEquals('Not implemented', $ex->getMessage());
    }
}
