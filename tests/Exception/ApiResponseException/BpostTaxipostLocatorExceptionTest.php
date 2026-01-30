<?php

namespace Bpost\BpostApiClient\Tests\Exception\BpostApiResponseException;

use Bpost\BpostApiClient\Exception\BpostApiResponseException\BpostTaxipostLocatorException;
use PHPUnit\Framework\TestCase;

class BpostTaxipostLocatorExceptionTest extends TestCase
{
    public function testGetMessage()
    {
        $ex = new BpostTaxipostLocatorException('Oops');
        $this->assertSame('Oops', $ex->getMessage());

        $ex = new BpostTaxipostLocatorException('');
        $this->assertSame('', $ex->getMessage());
    }
}
