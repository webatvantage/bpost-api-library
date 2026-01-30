<?php

namespace Tests\Exception\BpostApiResponseException;

use Bpost\BpostApiClient\Exception\BpostApiResponseException\BpostInvalidResponseException;
use PHPUnit\Framework\TestCase;

class BpostInvalidResponseExceptionTest extends TestCase
{
    public function testGetMessage()
    {
        $ex = new BpostInvalidResponseException('Oops');
        $this->assertSame('Invalid response: Oops', $ex->getMessage());

        $ex = new BpostInvalidResponseException();
        $this->assertSame('Invalid response', $ex->getMessage());
    }
}
