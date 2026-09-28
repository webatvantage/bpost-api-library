<?php

namespace Bpost\BpostApiClient\Common\BasicAttribute;

use Bpost\BpostApiClient\Common\BasicAttribute;
use Bpost\BpostApiClient\Exception\BpostLogicException\BpostInvalidLengthException;
use Bpost\BpostApiClient\Exception\BpostLogicException\BpostInvalidPatternException;

class EmailAddressCharacteristic extends BasicAttribute
{
    /**
     * @throws BpostInvalidLengthException
     * @throws BpostInvalidPatternException
     */
    public function validate()
    {
        $this->validateLength(40);
        $this->validateEmail();
    }

    /**
     * @return string
     */
    protected function getDefaultKey()
    {
        return 'emailAddress';
    }
}
