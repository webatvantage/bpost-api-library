<?php

namespace Tests\Exception\LogicException;

use Bpost\BpostApiClient\Exception\BpostLogicException\BpostInvalidWeightException;
use PHPUnit\Framework\TestCase;

class BpostInvalidWeightExceptionTest extends TestCase
{
    public function testGetMessage()
    {
        $ex = new BpostInvalidWeightException(32, 30);
        $this->assertSame('Invalid weight (32 kg), maximum is 30.', $ex->getMessage());
    }
}
