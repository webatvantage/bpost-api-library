<?php

namespace Bpost\BpostApiClient\Tests\Bpost\HttpRequestBuilder;

use Bpost\BpostApiClient\Bpost\HttpRequestBuilder\FetchProductConfigBuilder;
use PHPUnit\Framework\Attributes\DataProvider;
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
     */
	#[DataProvider('dataResults')]
    public function testResults(array $input, $url, $xml, $headers, $method, $isExpectXml): void
    {
        $builder = new FetchProductConfigBuilder();

        $this->assertEquals($url, $builder->getUrl());
        $this->assertEquals($method, $builder->getMethod());
        $this->assertEquals($xml, $builder->getXml());
        $this->assertEquals($isExpectXml, $builder->isExpectXml());
        $this->assertEquals($headers, $builder->getHeaders());
    }

    public static function dataResults(): array
    {
        return [
            [
                'input' => [],
                'url' => '/productconfig',
                'xml' => null,
                'headers' => ['Accept: application/vnd.bpost.shm-productConfiguration-v3.1+XML'],
                'method' => 'GET',
                'isExpectXml' => true,
			],
		];
    }
}
