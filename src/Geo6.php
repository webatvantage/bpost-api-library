<?php

namespace Bpost\BpostApiClient;

use Bpost\BpostApiClient\ApiCaller\ApiCaller;
use Bpost\BpostApiClient\Exception\BpostApiResponseException\BpostCurlException;
use Bpost\BpostApiClient\Exception\BpostApiResponseException\BpostInvalidXmlResponseException;
use Bpost\BpostApiClient\Exception\BpostApiResponseException\BpostTaxipostLocatorException;
use SimpleXMLElement;

/**
 * Geo6 class
 *
 * @author    Tijs Verkoyen <php-bpost@verkoyen.eu>
 *
 * @version   3.0.0
 *
 * @copyright Copyright (c), Tijs Verkoyen. All rights reserved.
 * @license   BSD License
 */
class Geo6
{
    // URL for the api
    const API_URL = 'https://pudo.bpost.be/Locator';

    // current version
    const VERSION = '3.7.0';

    /**
     * @see getPointType
     * @see getServicePointPageUrl
     */
    const POINT_TYPE_POST_OFFICE = 1;
    const POINT_TYPE_POST_POINT = 2;
    const POINT_TYPE_BPACK_247 = 4;
    const POINT_TYPE_CLICK_COLLECT_SHOP = 8;

    /** @var ApiCaller */
    private $apiCaller;

    /**
     * The timeout
     *
     * @var int
     */
    private $timeOut = 10;

    /**
     * The user agent
     *
     * @var string
     */
    private $userAgent;

    /**
     * Constructor
     *
     * @param string $partner Static parameter used for protection/statistics
     * @param string $appId   Static parameter used for protection/statistics
     */
    public function __construct()
    {
    }

    /**
     * @return ApiCaller
     */
    public function getApiCaller()
    {
        if ($this->apiCaller === null) {
            $this->apiCaller = new ApiCaller(new Logger());
        }

        return $this->apiCaller;
    }

    /**
     * @param ApiCaller $apiCaller
     */
    public function setApiCaller(ApiCaller $apiCaller)
    {
        $this->apiCaller = $apiCaller;
    }

    /**
     * Build the url to be called
     *
     * @param string $method
     * @param array  $parameters
     *
     * @return string
     */
    private function buildUrl($method, array $parameters = array())
    {
        return self::API_URL . '?' . http_build_query($parameters);
    }

    /**
     * Make the real call
     *
     * @param string $method
     * @param array  $parameters
     *
     * @return SimpleXMLElement
     *
     * @throws BpostCurlException
     * @throws BpostInvalidXmlResponseException
     * @throws BpostTaxipostLocatorException
     */
    protected function doCall(array $parameters): SimpleXMLElement
    {
        $options = array(
            CURLOPT_URL => self::API_URL,
            CURLOPT_USERAGENT => $this->getUserAgent(),
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $this->getTimeOut(),

            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query($parameters),
        );

        $this->getApiCaller()->doCall($options);

        // we expect XML so decode it
        $xml = @simplexml_load_string($this->getApiCaller()->getResponseBody());

        // validate xml
        if ($xml === false || (isset($xml->head) && isset($xml->body))) {
            throw new BpostInvalidXmlResponseException();
        }

        // catch generic errors
        if (isset($xml['type']) && (string) $xml['type'] == 'TaxipostLocatorError') {
            throw new BpostTaxipostLocatorException((string) $xml->txt, (int) $xml->status);
        }

        // return
        return $xml;
    }

    /**
     * Set the timeout
     * After this time the request will stop. You should handle any errors triggered by this.
     *
     * @param int $seconds The timeout in seconds.
     */
    public function setTimeOut($seconds)
    {
        $this->timeOut = (int) $seconds;
    }

    /**
     * Get the timeout that will be used
     *
     * @return int
     */
    public function getTimeOut()
    {
        return (int) $this->timeOut;
    }

    /**
     * Get the useragent that will be used.
     * Our version will be prepended to yours.
     * It will look like: "PHP Bpost/<version> <your-user-agent>"
     *
     * @return string
     */
    public function getUserAgent()
    {
        return (string) 'PHP Bpost Geo6/' . self::VERSION . ' ' . $this->userAgent;
    }

    /**
     * Set the user-agent for you application
     * It will be appended to ours, the result will look like: "PHP Bpost/<version> <your-user-agent>"
     *
     * @param string $userAgent Your user-agent, it should look like <app-name>/<app-version>.
     */
    public function setUserAgent($userAgent)
    {
        $this->userAgent = (string) $userAgent;
    }

    /**
     * @param int    $id
     * @param string $language
     * @param int    $type
     * @param string $country
     *
     * @return string
     *
     * @see getPointType to feed the param $type
     */
    public function getServicePointPageUrl($id, $language = 'nl', $type = 3, $country = 'BE')
    {
        $parameters = array(
            'Id' => (string) $id,
            'Language' => (string) $language,
            'Type' => (int) $type,
            'Country' => (string) $country,
        );

        return $this->buildUrl('page', $parameters);
    }

    /**
     * @param int    $id
     * @param string $language
     * @param int    $type
     * @param string $country
     *
     * @return string
     *
     * @deprecated Renamed
     * @see        getServicePointPageUrl
     */
    public function getServicePointPage($id, $language = 'nl', $type = 3, $country = 'BE')
    {
        return $this->getServicePointPageUrl($id, $language, $type, $country);
    }

	/**
     * @param bool $withPostOffice
     * @param bool $withPostPoint
     * @param bool $withBpack247
     * @param bool $withClickAndCollectShop
     *
     * @return int
     */
    public function getPointType(
        $withPostOffice = true,
        $withPostPoint = true,
        $withBpack247 = false,
        $withClickAndCollectShop = false
    ) {
        return
            ($withPostOffice ? self::POINT_TYPE_POST_OFFICE : 0)
            + ($withPostPoint ? self::POINT_TYPE_POST_POINT : 0)
            + ($withBpack247 ? self::POINT_TYPE_BPACK_247 : 0)
            + ($withClickAndCollectShop ? self::POINT_TYPE_CLICK_COLLECT_SHOP : 0);
    }
}
