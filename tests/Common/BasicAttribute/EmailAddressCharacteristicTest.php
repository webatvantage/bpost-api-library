<?php

namespace Bpost\BpostApiClient\Tests\Common\BasicAttribute;

use Bpost\BpostApiClient\BpostException;
use Bpost\BpostApiClient\Common\BasicAttribute\EmailAddressCharacteristic;
use Bpost\BpostApiClient\Exception\BpostLogicException\BpostInvalidLengthException;
use Bpost\BpostApiClient\Exception\BpostLogicException\BpostInvalidPatternException;
use PHPUnit\Framework\TestCase;

class EmailAddressCharacteristicTest extends TestCase
{
    public function testItAcceptsValidAddresses()
    {
        $values = array(
            'pomme2016@antidot.com',
            'first.last+tag@sub.domain.co.uk',
            // A long TLD. The pattern this replaced was assumed to cap these at four characters,
            // but its trailing + let the group repeat, so they were always accepted.
            'info@bpost.online',
        );

        foreach ($values as $value) {
            try {
                $test = new EmailAddressCharacteristic($value);
                $this->assertEquals($value, $test->getValue());
            } catch (BpostException $ex) {
                $this->fail('Exception launched for valid value: "' . $value . '"');
            }
        }
    }

    public function testItRejectsAddressesLongerThanTheLimit()
    {
        $value = 'myBeautifulAndLongEmailAddressFor2016@antidot-company-based-at-Brussels.com';

        $this->expectException(BpostInvalidLengthException::class);

        new EmailAddressCharacteristic($value);
    }

    /**
     * The first three were accepted by the pattern this replaced: it allowed a dot anywhere in the
     * local part, including at either end and doubled up. The next two allowed a hyphen at the
     * edge of a domain label.
     */
    public function testItRejectsMalformedAddresses()
    {
        $values = array(
            'leading dot' => '.pomme@antidot.com',
            'trailing dot' => 'pomme.@antidot.com',
            'consecutive dots' => 'pom..me@antidot.com',
            'leading hyphen in domain' => 'pomme@-antidot.com',
            'trailing hyphen in domain' => 'pomme@antidot-.com',
            'no at sign' => 'pomme.antidot.com',
            'no domain' => 'pomme@',
            'space' => 'po mme@antidot.com',
        );

        foreach ($values as $label => $value) {
            try {
                new EmailAddressCharacteristic($value);
                $this->fail('Exception uncaught for ' . $label . ': "' . $value . '"');
            } catch (BpostInvalidPatternException $ex) {
                $this->assertTrue(true);
            }
        }
    }
}
