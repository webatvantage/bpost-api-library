<?php

namespace Bpost\BpostApiClient\Tests\Bpost\HttpRequestBuilder;

use Bpost\BpostApiClient\Bpost\HttpRequestBuilder\CreateLabelForBoxBuilder;
use Bpost\BpostApiClient\Common\ValidatedValue\LabelFormat;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class CreateLabelForBoxTest extends TestCase
{
    /**
     * @param array  $input
     * @param string $url
     * @param array  $headers
     * @param string $xml
     * @param string $method
     * @param bool   $isExpectXml
     *
     * @return void
     */
	#[DataProvider('dataResults')]
    public function testResults(array $input, $url, $headers, $xml, $method, $isExpectXml)
    {
        $builder = new CreateLabelForBoxBuilder($input[0], $input[1], $input[2], $input[3]);
        $this->assertEquals($url, $builder->getUrl());
        $this->assertEquals($method, $builder->getMethod());
        $this->assertEquals($xml, $builder->getXml());
        $this->assertEquals($isExpectXml, $builder->isExpectXml());
        $this->assertEquals($headers, $builder->getHeaders());
    }

    public static function dataResults(): array
    {
        $labelA4 = new LabelFormat(LabelFormat::FORMAT_A4);
        $labelA6 = new LabelFormat(LabelFormat::FORMAT_A6);

        return [
            [
                'input' => ['123', $labelA4, false, false],
                'url' => '/boxes/123/labels/A4',
                'headers' => static::getHeadersForImage(),
                'xml' => null,
                'method' => 'GET',
                'isExpectXml' => true,
			],
            [
                'input' => ['123', $labelA6, false, false],
                'url' => '/boxes/123/labels/A6',
                'headers' => static::getHeadersForImage(),
                'xml' => null,
                'method' => 'GET',
                'isExpectXml' => true,
			],
            [
                'input' => ['123', $labelA4, true, false],
                'url' => '/boxes/123/labels/A4',
                'headers' => static::getHeadersForPdf(),
                'xml' => null,
                'method' => 'GET',
                'isExpectXml' => true,
			],
            [
                'input' => ['123', $labelA6, true, false],
                'url' => '/boxes/123/labels/A6',
                'headers' => static::getHeadersForPdf(),
                'xml' => null,
                'method' => 'GET',
                'isExpectXml' => true,
			],
            [
                'input' => ['123', $labelA4, false, true],
                'url' => '/boxes/123/labels/A4/withReturnLabels',
                'headers' => static::getHeadersForImage(),
                'xml' => null,
                'method' => 'GET',
                'isExpectXml' => true,
			],
            [
                'input' => ['123', $labelA6, false, true],
                'url' => '/boxes/123/labels/A6/withReturnLabels',
                'headers' => static::getHeadersForImage(),
                'xml' => null,
                'method' => 'GET',
                'isExpectXml' => true,
			],
            [
                'input' => ['123', $labelA4, true, true],
                'url' => '/boxes/123/labels/A4/withReturnLabels',
                'headers' => static::getHeadersForPdf(),
                'xml' => null,
                'method' => 'GET',
                'isExpectXml' => true,
			],
            [
                'input' => ['123', $labelA6, true, true],
                'url' => '/boxes/123/labels/A6/withReturnLabels',
                'headers' => static::getHeadersForPdf(),
                'xml' => null,
                'method' => 'GET',
                'isExpectXml' => true,
			],
		];
    }

    private static function getHeadersForPdf(): array
    {
        return [
            'Accept: application/vnd.bpost.shm-label-pdf-v3.4+XML',
            'Content-Type: application/vnd.bpost.shm-labelRequest-v3+XML',
		];
    }

    private static function getHeadersForImage(): array
    {
        return [
            'Accept: application/vnd.bpost.shm-label-image-v3.4+XML',
            'Content-Type: application/vnd.bpost.shm-labelRequest-v3+XML',
		];
    }
}
