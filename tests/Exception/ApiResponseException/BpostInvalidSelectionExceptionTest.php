<?php

namespace Bpost\BpostApiClient\Tests\Exception\BpostApiResponseException;

use Bpost\BpostApiClient\Exception\BpostApiResponseException\BpostInvalidSelectionException;
use PHPUnit\Framework\TestCase;

class BpostInvalidSelectionExceptionTest extends TestCase
{
    public function testGetMessage()
    {
        $ex = new BpostInvalidSelectionException('Oops');
        $this->assertSame('Oops', $ex->getMessage());

        $ex = new BpostInvalidSelectionException();
        $this->assertSame('', $ex->getMessage());
    }
}
