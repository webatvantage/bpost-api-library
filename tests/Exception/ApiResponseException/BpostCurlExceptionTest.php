<?php

namespace Bpost\BpostApiClient\Tests\Exception\BpostApiResponseException;

use Bpost\BpostApiClient\Exception\BpostApiResponseException\BpostCurlException;
use PHPUnit\Framework\TestCase;

class BpostCurlExceptionTest extends TestCase
{
    public function testGetMessage()
    {
        $ex = new BpostCurlException('Oops', 500);
        $this->assertSame('Oops', $ex->getMessage());
        $this->assertSame(500, $ex->getCode());

        $ex = new BpostCurlException('', 200);
        $this->assertSame('', $ex->getMessage());
        $this->assertSame(200, $ex->getCode());
    }
}
