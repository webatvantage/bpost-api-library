<?php

namespace Bpost\BpostApiClient\Tests\Bpost\HttpRequestBuilder;

use Bpost\BpostApiClient\Bpost\HttpRequestBuilder\FetchProductConfigBuilder;
use PHPUnit\Framework\TestCase;

class FetchProductConfigTest extends TestCase
{
    /**
     * @param array  $input
     * @param string $url
     * @param string $xml
     * @param string $method
     * @param bool   $isExpectXml
     * @param array  $headers
     *
     * @return void
     *
     * @dataProvider dataResults
     */
    public function testResults(array $input, $url, $xml, $headers, $method, $isExpectXml)
    {
        $builder = new FetchProductConfigBuilder();

        $this->assertSame($url, $builder->getUrl());
        $this->assertSame($method, $builder->getMethod());
        $this->assertSame($xml, $builder->getXml());
        $this->assertSame($isExpectXml, $builder->isExpectXml());
        $this->assertSame($headers, $builder->getHeaders());
    }

    public function dataResults()
    {
        return array(
            array(
                'input' => array(),
                'url' => '/productconfig',
                'xml' => null,
                'headers' => array('Accept: application/vnd.bpost.shm-productConfiguration-v3.1+XML'),
                'method' => 'GET',
                'isExpectXml' => true,
            ),
        );
    }
}
