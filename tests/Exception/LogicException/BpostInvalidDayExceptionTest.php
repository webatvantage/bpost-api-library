<?php

namespace Bpost\BpostApiClient\Tests\Exception\LogicException;

use Bpost\BpostApiClient\Exception\BpostLogicException\BpostInvalidDayException;
use PHPUnit\Framework\TestCase;

class BpostInvalidDayExceptionTest extends TestCase
{
    public function testGetMessage()
    {
        $ex = new BpostInvalidDayException('unicorn', array('Monday', 'Tuesday'));
        $this->assertSame('Invalid value (unicorn) for day, possible values are: Monday, Tuesday.', $ex->getMessage());
    }
}
