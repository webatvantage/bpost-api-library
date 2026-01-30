<?php

namespace Bpost\BpostApiClient\Tests\Exception\BpostApiResponseException;

use Bpost\BpostApiClient\Exception\BpostApiResponseException\BpostApiBusinessException;
use PHPUnit\Framework\TestCase;

class BpostApiBusinessExceptionTest extends TestCase
{
    public function testGetMessage()
    {
        $ex = new BpostApiBusinessException('Oops', 500);
        $this->assertSame('Oops', $ex->getMessage());
        $this->assertSame(500, $ex->getCode());

        $ex = new BpostApiBusinessException('', 200);
        $this->assertSame('', $ex->getMessage());
        $this->assertSame(200, $ex->getCode());
    }
}
