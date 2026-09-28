<?php

namespace Webatvantage\Bpost\Api\Tests\ApiAdapter;

use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use SimpleXMLElement;
use Webatvantage\Bpost\Api\ApiAdapter\HttpApiAdapter;
use Webatvantage\Bpost\Api\Enums\Method;
use Webatvantage\Bpost\Api\Exceptions\BusinessException;
use Webatvantage\Bpost\Api\Exceptions\InvalidResponseException;
use Webatvantage\Bpost\Api\Exceptions\SystemException;
use Webatvantage\Bpost\Api\Exceptions\TransporterException;
use Webatvantage\Bpost\Api\Exceptions\UnserializableResponseException;
use Webatvantage\Bpost\Api\Requests\Request;
use Webatvantage\Bpost\Api\Tests\TestCase;

class HttpApiAdapterTest extends TestCase
{
	public function test_it_parses_an_xml_response()
	{
		$this->mockResponse(200, '<labels><label><barcode>323212345</barcode></label></labels>');

		$result = $this->adapter()->request(new Request(Method::GET, '/orders/ref'));

		$this->assertInstanceOf(SimpleXMLElement::class, $result);
		$this->assertSame('323212345', (string)$result->label->barcode);
	}

	public function test_it_returns_an_empty_string_for_an_empty_created_response()
	{
		$this->mockResponse(201, '');

		$this->assertSame('', $this->adapter()->request(new Request(Method::POST, '/orders')));
	}

	public function test_the_debug_callback_receives_the_request_and_the_response()
	{
		$this->mockResponse(400, '<businessException><message>Invalid weight</message></businessException>');

		$seen = [];
		$adapter = $this->adapter()->setDebugCallback(function (RequestInterface $request, ResponseInterface $response) use (&$seen) {
			$seen = [$request, $response, (string)$response->getBody()];
		});

		try
		{
			$adapter->request(new Request(Method::POST, '/orders', body: '<order/>'));
		}
		catch (BusinessException)
		{
		}

		[$request, $response, $body] = $seen;

		$this->assertSame('POST', $request->getMethod());
		$this->assertSame('https://example.test/orders', (string)$request->getUri());
		$this->assertSame('<order/>', (string)$request->getBody());
		$this->assertSame(400, $response->getStatusCode());
		$this->assertStringContainsString('Invalid weight', $body);
	}

	public function test_it_merges_default_headers_with_request_headers()
	{
		$this->mockResponse(200, '<ok/>');

		$request = new Request(Method::GET, '/orders/ref', headers: ['Accept' => 'application/vnd.bpost.shm-order-v3.5+XML']);
		$this->adapter(['Authorization' => 'Basic abc'])->request($request);

		$sent = $this->lastRequest();
		$this->assertSame('Basic abc', $sent->getHeaderLine('Authorization'));
		$this->assertSame('application/vnd.bpost.shm-order-v3.5+XML', $sent->getHeaderLine('Accept'));
	}

	public function test_it_maps_a_business_exception_document()
	{
		$this->mockResponse(409, <<<'XML'
			<ns2:businessException xmlns="http://schema.post.be/common/exception/v1/"
			xmlns:ns2="http://schema.post.be/api/shm/v1/">
				<code>409</code>
				<message>The order is in CANCELLED state and cannot be modified anymore.</message>
			</ns2:businessException>
			XML);

		try
		{
			$this->adapter()->request(new Request(Method::POST, '/orders/ref'));
			$this->fail('Expected a BusinessException.');
		}
		catch (BusinessException $exception)
		{
			$this->assertSame('The order is in CANCELLED state and cannot be modified anymore.', $exception->getMessage());
			$this->assertSame(409, $exception->statusCode);
			$this->assertStringContainsString('businessException', $exception->body);
		}
	}

	public function test_it_maps_a_system_exception_document()
	{
		$this->mockResponse(500, <<<'XML'
			<systemException xmlns="http://schema.post.be/api/shm/common/v2/"
			xmlns:ns2="http://schema.post.be/common/exception/v1/">
				<ns2:message>An unexpected error occurred, token f35c0f13</ns2:message>
			</systemException>
			XML);

		$this->expectException(SystemException::class);
		$this->expectExceptionMessage('An unexpected error occurred, token f35c0f13');

		$this->adapter()->request(new Request(Method::POST, '/orders'));
	}

	/**
	 * The old ApiCaller read the content type from the wrong curl_getinfo() key, so a plain-text
	 * error body was discarded and the caller got an exception with an empty message.
	 */
	public function test_it_keeps_a_plain_text_error_body_in_the_message()
	{
		$this->mockResponse(400, 'Order reference is required', ['Content-Type' => 'text/plain']);

		try
		{
			$this->adapter()->request(new Request(Method::POST, '/orders'));
			$this->fail('Expected an InvalidResponseException.');
		}
		catch (InvalidResponseException $exception)
		{
			$this->assertStringContainsString('Order reference is required', $exception->getMessage());
			$this->assertSame('Order reference is required', $exception->body);
		}
	}

	public function test_it_reports_an_empty_error_body_rather_than_an_empty_message()
	{
		$this->mockResponse(404, '');

		$this->expectException(InvalidResponseException::class);
		$this->expectExceptionMessage('bpost answered HTTP 404 with an empty body.');

		$this->adapter()->request(new Request(Method::GET, '/orders/nope'));
	}

	public function test_it_rejects_a_successful_response_that_is_not_xml()
	{
		$this->mockResponse(200, '<html><body>Gateway</body>');

		$this->expectException(UnserializableResponseException::class);

		$this->adapter()->request(new Request(Method::GET, '/orders/ref'));
	}

	public function test_it_returns_the_raw_body_when_xml_is_not_expected()
	{
		$this->mockResponse(200, 'not xml at all');

		$request = new Request(Method::GET, '/page', expectsXml: false);

		$this->assertSame('not xml at all', $this->adapter()->request($request));
	}

	public function test_it_wraps_transport_failures()
	{
		$mock = new MockHandler([
			new \GuzzleHttp\Exception\ConnectException(
				'Could not resolve host',
				new \GuzzleHttp\Psr7\Request('GET', '/orders'),
			),
		]);

		$adapter = new HttpApiAdapter('https://example.test', [], ['handler' => HandlerStack::create($mock)]);

		$this->expectException(TransporterException::class);
		$this->expectExceptionMessage('Could not resolve host');

		$adapter->request(new Request(Method::GET, '/orders'));
	}
}
