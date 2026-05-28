<?php

namespace Bpost\BpostApiClient\Geo6;

use Bpost\BpostApiClient\Exception\BpostApiResponseException\BpostCurlException;
use Bpost\BpostApiClient\Exception\BpostApiResponseException\BpostInvalidXmlResponseException;
use Bpost\BpostApiClient\Exception\BpostApiResponseException\BpostTaxipostLocatorException;
use Bpost\BpostApiClient\Geo6;

class Geo6Partner extends Geo6
{
    public function __construct(private readonly string $partner, private readonly string $appId)
    {
        parent::__construct();
    }

    /**
     * The GetNearestServicePoints web service delivers the nearest bpost pick-up points to a location
     *
     * @param string $street   Street name
     * @param string $number   Street number
     * @param string $zone     Postal code and/or city
     * @param string $language Language, possible values are: nl, fr
     * @param int    $type     Requested point type, possible values are:
     *                         - 1: Post Office
     *                         - 2: Post Point
     *                         - 3: (1+2, Post Office + Post Point)
     *                         - 4: bpack 24/7
     *                         - 7: (1+2+4, Post Office + Post Point + bpack 24/7)
     * @param int    $limit
     * @param string $country  Country: "BE", "FR"...
     *
     * @return array
     *
     * @throws BpostCurlException
     * @throws BpostInvalidXmlResponseException
     * @throws BpostTaxipostLocatorException
     */
    // public function getNearestServicePoint($street, $number, $zone, $country = 'BE', $language = 'nl', $type = 3, $limit = 10)
    public function getNearestServicePoint($street, $number, $zone, $language = 'nl', $type = 3, $limit = 10, $country = 'BE')
    {
        $parameters = array(
            'Street' => (string) $street,
            'Number' => (string) $number,
            'Zone' => (string) $zone,
            'Country' => (string) $country,
            'Language' => (string) $language,
            'Type' => (int) $type,
            'Limit' => (int) $limit,
        );

        $xml = $this->doCall('search', $parameters);

        if (!isset($xml->PoiList->Poi)) {
            throw new BpostInvalidXmlResponseException();
        }

        $pois = array();
        foreach ($xml->PoiList->Poi as $poi) {
            $pois[] = array(
                'poi' => Poi::createFromXML($poi),
                'distance' => (float) $poi->Distance,
            );
        }

        return $pois;
    }

    /**
     * The GetServicePointDetails web service delivers the details for a bpost
     * pick up point referred to by its identifier.
     *
     * @param string $id       Requested point identifier
     * @param string $language Language, possible values: nl, fr
     * @param int    $type     Requested point type, possible values are:
     *                         - 1: Post Office
     *                         - 2: Post Point
     *                         - 4: bpack 24/7
     * @param string $country  Country: "BE", "FR"...
     *
     * @return Poi
     *
     * @throws BpostCurlException
     * @throws BpostInvalidXmlResponseException
     * @throws BpostTaxipostLocatorException
     */
    public function getServicePointDetails($id, $language = 'nl', $type = 3, $country = 'BE')
    {
        $parameters = array(
            'Id' => (string) $id,
            'Language' => (string) $language,
            'Type' => (int) $type,
            'Country' => (string) $country,
        );

        $xml = $this->doCall('info', $parameters);

        if (!isset($xml->Poi)) {
            throw new BpostInvalidXmlResponseException();
        }

        return Poi::createFromXML($xml->Poi);
    }
}
