<?php

namespace Bpost\BpostApiClient\Geo6;

use Bpost\BpostApiClient\Exception\BpostApiResponseException\BpostCurlException;
use Bpost\BpostApiClient\Exception\BpostApiResponseException\BpostInvalidXmlResponseException;
use Bpost\BpostApiClient\Exception\BpostApiResponseException\BpostTaxipostLocatorException;
use Bpost\BpostApiClient\Geo6;
use SimpleXMLElement;

class Geo6Account extends Geo6
{
    private const METHOD_GET_ALL_SERVICE_POINTS = 'getallservicepoints';

    public function __construct(private readonly string $account)
    {
        parent::__construct();
    }

    /**
     * @throws BpostCurlException
     * @throws BpostTaxipostLocatorException
     * @throws BpostInvalidXmlResponseException
     *
     * @return list<Poi>
     */
    public function getServicePoints(string $language = 'nl', string $country = 'BE', ?int $type = null, ?string $zip = null): array
    {
        $parameters = [
            'Account' => $this->account,
            'Function' => self::METHOD_GET_ALL_SERVICE_POINTS,
            'Format' => 'xml',
            'Language' => $language,
            'Country' => $country,
        ];

        if ($type !== null)
        {
            $parameters['Type'] = $type;
        }

        if ($zip !== null)
        {
            $parameters['Zip'] = $zip;
        }

        $xml = $this->doCall($parameters);
        $points = $xml->PickupPointList->Point ?? [];

        $servicePoints = [];
        foreach ($points as $point)
        {
            // Encapsulate the data into the 'Record' tag to allow for uniform data handling
            $data = new SimpleXMLElement('<root><Record/></root>');
            $record = $data->Record;

            $domRecord = dom_import_simplexml($record);
            $domPoint = dom_import_simplexml($point);
            $imported = $domRecord->ownerDocument->importNode($domPoint, true);

            foreach ($imported->childNodes as $child)
            {
                $domRecord->appendChild($child->cloneNode(true));
            }

            $servicePoints[] = Poi::createFromXml($data);
        }

        return $servicePoints;
    }
}
