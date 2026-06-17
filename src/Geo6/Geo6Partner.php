<?php

namespace Bpost\BpostApiClient\Geo6;

use Bpost\BpostApiClient\Exception\BpostApiResponseException\BpostCurlException;
use Bpost\BpostApiClient\Exception\BpostApiResponseException\BpostInvalidXmlResponseException;
use Bpost\BpostApiClient\Exception\BpostApiResponseException\BpostTaxipostLocatorException;
use Bpost\BpostApiClient\Geo6;

class Geo6Partner extends Geo6
{
    private const METHOD_GET_NEAREST_SERVICE_POINTS = 'search';

    private const METHOD_GET_SERVICE_POINT_DETAILS = 'info';

    private const METHOD_GET_SERVICE_POINT_PAGE = 'page';

    public function __construct(private readonly string $partner, private readonly string $appId)
    {
        parent::__construct();
    }

    /**
     * The GetNearestServicePoints web service delivers the nearest bpost pick-up points to a location
     *
     * @param string $street Street name
     * @param string $number Street number
     * @param string $zone Postal code and/or city
     * @param string $language Language, possible values are: nl, fr
     * @param int $type Requested point type, possible values are:
     *                         - 1: Post Office
     *                         - 2: Post Point
     *                         - 3: (1+2, Post Office + Post Point)
     *                         - 4: bpack 24/7
     *                         - 7: (1+2+4, Post Office + Post Point + bpack 24/7)
     * @param int $limit
     * @param string $country Country: "BE", "FR"...
     *
     * @return array
     *
     * @throws BpostCurlException
     * @throws BpostInvalidXmlResponseException
     * @throws BpostTaxipostLocatorException
     */
    public function getNearestServicePoint(string $street, string $number, string $zone, string $country = 'BE', string $language = 'NL', int $type = 3, int $limit = 10): array
    {
        $parameters = array(
            'Function' => self::METHOD_GET_NEAREST_SERVICE_POINTS,
            'Partner' => $this->partner,
            'Street' => $street,
            'Number' => $number,
            'Zone' => $zone,
            'Country' => $country,
            'Language' => $language,
            'Type' => $type,
            'Limit' => $limit,
        );

        $xml = $this->doCall($parameters);

        if (!isset($xml->PoiList->Poi))
        {
            throw new BpostInvalidXmlResponseException();
        }

        $servicePoints = array();
        foreach ($xml->PoiList->Poi as $poi)
        {
            $servicePoints[] = array(
                'poi' => Poi::createFromXML($poi),
                'distance' => (float)$poi->Distance,
            );
        }

        return $servicePoints;
    }

    /**
     * The GetServicePointDetails web service delivers the details for a bpost
     * pick up point referred to by its identifier.
     *
     * @param string $id Requested point identifier
     * @param string $language Language, possible values: nl, fr
     * @param int $type Requested point type, possible values are:
     *                         - 1: Post Office
     *                         - 2: Post Point
     *                         - 4: bpack 24/7
     * @param string $country Country: "BE", "FR"...
     *
     * @return Poi
     *
     * @throws BpostCurlException
     * @throws BpostInvalidXmlResponseException
     * @throws BpostTaxipostLocatorException
     */
    public function getServicePointDetails(string $id, string $language = 'NL', int $type = 3, string $country = 'BE'): Poi
    {
        $parameters = array(
            'Function' => self::METHOD_GET_SERVICE_POINT_DETAILS,
            'Partner' => $this->partner,
            'Language' => $language,
            'Id' => $id,
            'Type' => $type,
            'Country' => $country,
        );

        $xml = $this->doCall($parameters);

        if (!isset($xml->Poi))
        {
            throw new BpostInvalidXmlResponseException();
        }

        return Poi::createFromXML($xml->Poi);
    }
}
