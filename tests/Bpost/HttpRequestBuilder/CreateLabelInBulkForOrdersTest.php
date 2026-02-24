<?php

namespace Bpost\BpostApiClient\Tests\Bpost\HttpRequestBuilder;

use Bpost\BpostApiClient\Bpost\HttpRequestBuilder\CreateLabelInBulkForOrdersBuilder;
use Bpost\BpostApiClient\Common\ValidatedValue\LabelFormat;
use DOMException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class CreateLabelInBulkForOrdersTest extends TestCase
{
    /**
     * @param array  $input
     * @param string $url
     * @param string $xml
     * @param string $method
     * @param bool   $isExpectXml
     * @param array  $headers
     *
     * @throws DOMException
     */
	#[DataProvider('dataResults')]
    public function testResults(array $input, $url, $xml, $method, $isExpectXml, $headers)
    {
        $builder = new CreateLabelInBulkForOrdersBuilder($input[0], $input[1], $input[2], $input[3], $input[4]);

        $this->assertSame($url, $builder->getUrl());
        $this->assertSame($method, $builder->getMethod());
        $this->assertSame($xml, $builder->getXml());
        $this->assertSame($isExpectXml, $builder->isExpectXml());
        $this->assertSame($headers, $builder->getHeaders());
    }

    public static function dataResults(): array
    {
        $references = array('order_ref_1', 'order_ref_2');
        $labelA4 = new LabelFormat(LabelFormat::FORMAT_A4);
        $labelA6 = new LabelFormat(LabelFormat::FORMAT_A6);

        return [
            [
                'input' => [$references, $labelA4, false, false, false],
                'url' => '/labels/A4',
                'xml' => static::getExpectedXml(),
                'method' => 'POST',
                'isExpectXml' => true,
                'headers' => static::getHeadersForImage(),
			],
            [
                'input' => [$references, $labelA6, false, false, false],
                'url' => '/labels/A6',
                'xml' => static::getExpectedXml(),
                'method' => 'POST',
                'isExpectXml' => true,
                'headers' => static::getHeadersForImage(),
			],
            [
                'input' => [$references, $labelA4, true, false, false],
                'url' => '/labels/A4',
                'xml' => static::getExpectedXml(),
                'method' => 'POST',
                'isExpectXml' => true,
                'headers' => static::getHeadersForPdf(),
			],
            [
                'input' => [$references, $labelA6, true, false, false],
                'url' => '/labels/A6',
                'xml' => static::getExpectedXml(),
                'method' => 'POST',
                'isExpectXml' => true,
                'headers' => static::getHeadersForPdf(),
			],

            [
                'input' => [$references, $labelA4, false, true, false],
                'url' => '/labels/A4/withReturnLabels',
                'xml' => static::getExpectedXml(),
                'method' => 'POST',
                'isExpectXml' => true,
                'headers' => static::getHeadersForImage(),
			],
            [
                'input' => [$references, $labelA6, false, true, false],
                'url' => '/labels/A6/withReturnLabels',
                'xml' => static::getExpectedXml(),
                'method' => 'POST',
                'isExpectXml' => true,
                'headers' => static::getHeadersForImage(),
			],
            [
                'input' => [$references, $labelA4, true, true, false],
                'url' => '/labels/A4/withReturnLabels',
                'xml' => static::getExpectedXml(),
                'method' => 'POST',
                'isExpectXml' => true,
                'headers' => static::getHeadersForPdf(),
			],
            [
                'input' => [$references, $labelA6, true, true, false],
                'url' => '/labels/A6/withReturnLabels',
                'xml' => static::getExpectedXml(),
                'method' => 'POST',
                'isExpectXml' => true,
                'headers' => static::getHeadersForPdf(),
			],

            [
                'input' => [$references, $labelA4, false, false, true],
                'url' => '/labels/A4?forcePrinting=true',
                'xml' => static::getExpectedXml(),
                'method' => 'POST',
                'isExpectXml' => true,
                'headers' => static::getHeadersForImage(),
			],
            [
                'input' => [$references, $labelA6, false, false, true],
                'url' => '/labels/A6?forcePrinting=true',
                'xml' => static::getExpectedXml(),
                'method' => 'POST',
                'isExpectXml' => true,
                'headers' => static::getHeadersForImage(),
			],
            [
                'input' => [$references, $labelA4, true, false, true],
                'url' => '/labels/A4?forcePrinting=true',
                'xml' => static::getExpectedXml(),
                'method' => 'POST',
                'isExpectXml' => true,
                'headers' => static::getHeadersForPdf(),
			],
            [
                'input' => [$references, $labelA6, true, false, true],
                'url' => '/labels/A6?forcePrinting=true',
                'xml' => static::getExpectedXml(),
                'method' => 'POST',
                'isExpectXml' => true,
                'headers' => static::getHeadersForPdf(),
			],

            [
                'input' => [$references, $labelA4, false, true, true],
                'url' => '/labels/A4/withReturnLabels?forcePrinting=true',
                'xml' => static::getExpectedXml(),
                'method' => 'POST',
                'isExpectXml' => true,
                'headers' => static::getHeadersForImage(),
			],
            [
                'input' => [$references, $labelA6, false, true, true],
                'url' => '/labels/A6/withReturnLabels?forcePrinting=true',
                'xml' => static::getExpectedXml(),
                'method' => 'POST',
                'isExpectXml' => true,
                'headers' => static::getHeadersForImage(),
			],
            [
                'input' => [$references, $labelA4, true, true, true],
                'url' => '/labels/A4/withReturnLabels?forcePrinting=true',
                'xml' => static::getExpectedXml(),
                'method' => 'POST',
                'isExpectXml' => true,
                'headers' => static::getHeadersForPdf(),
			],
            [
                'input' => [$references, $labelA6, true, true, true],
                'url' => '/labels/A6/withReturnLabels?forcePrinting=true',
                'xml' => static::getExpectedXml(),
                'method' => 'POST',
                'isExpectXml' => true,
                'headers' => static::getHeadersForPdf(),
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

    private static function getExpectedXml(): string
    {
        return <<< XML
<?xml version="1.0" encoding="UTF-8"?>
<batchLabels xmlns="http://schema.post.be/shm/deepintegration/v3/">
  <order>order_ref_1</order>
  <order>order_ref_2</order>
</batchLabels>

XML;
    }
}
