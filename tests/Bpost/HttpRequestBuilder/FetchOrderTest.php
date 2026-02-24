<?php

namespace Bpost\BpostApiClient\Tests\Bpost\HttpRequestBuilder;

use Bpost\BpostApiClient\Bpost\HttpRequestBuilder\FetchOrderBuilder;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class FetchOrderTest extends TestCase
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
        $builder = new FetchOrderBuilder($input[0]);

        $this->assertSame($url, $builder->getUrl());
        $this->assertSame($method, $builder->getMethod());
        $this->assertSame($xml, $builder->getXml());
        $this->assertSame($isExpectXml, $builder->isExpectXml());
        $this->assertSame($headers, $builder->getHeaders());
    }

    public static function dataResults(): array
    {
        return [
            [
                'input' => ['123'],
                'url' => '/orders/123',
                'xml' => null,
                'headers' => ['Accept: application/vnd.bpost.shm-order-v3.5+XML'],
                'method' => 'GET',
                'isExpectXml' => true,
			],
		];
    }
}
