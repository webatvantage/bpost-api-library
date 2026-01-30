<?php

namespace Tests\Exception\BpostApiResponseException;

use Bpost\BpostApiClient\Exception\BpostApiResponseException\BpostApiSystemException;
use PHPUnit\Framework\TestCase;

class BpostApiSystemExceptionTest extends TestCase
{
    public function testGetMessage()
    {
        $ex = new BpostApiSystemException('Oops', 500);
        $this->assertSame('Oops', $ex->getMessage());
        $this->assertSame(500, $ex->getCode());

        $ex = new BpostApiSystemException('', 200);
        $this->assertSame('', $ex->getMessage());
        $this->assertSame(200, $ex->getCode());
    }
}
