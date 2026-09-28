<?php

namespace Webatvantage\Bpost\Api\Tests\Requests;

use Webatvantage\Bpost\Api\Enums\Language;
use Webatvantage\Bpost\Api\Enums\Method;
use Webatvantage\Bpost\Api\Requests\Request;
use Webatvantage\Bpost\Api\Tests\TestCase;

class RequestTest extends TestCase
{
	public function test_it_builds_a_uri_without_parameters()
	{
		$this->assertSame('/orders/ref', new Request(Method::GET, '/orders/ref')->getUri());
	}

	public function test_it_appends_parameters()
	{
		$request = new Request(Method::GET, '/Locator')
			->addParameter('Function', 'search')
			->addParameter('Zone', '1000');

		$this->assertSame('/Locator?Function=search&Zone=1000', $request->getUri());
	}

	/**
	 * bpost documents AttributeFilter as a repeated key and rejects the indexed form that Guzzle's
	 * Query::build would produce.
	 */
	public function test_it_repeats_an_array_parameter_without_indices()
	{
		$request = new Request(Method::GET, '/Locator')
			->addParameter('AttributeFilter', ['NIGHTDELIVERY:TRUE', 'LOCKERTYPE:CLASSIC']);

		$this->assertSame(
			'/Locator?AttributeFilter=NIGHTDELIVERY%3ATRUE&AttributeFilter=LOCKERTYPE%3ACLASSIC',
			$request->getUri(),
		);
	}

	public function test_it_unwraps_backed_enums_and_booleans()
	{
		$request = new Request(Method::GET, '/Locator')
			->addParameter('Language', Language::NL)
			->addParameter('CheckDate', true)
			->addParameter('CheckList', false);

		$this->assertSame('/Locator?Language=NL&CheckDate=1&CheckList=0', $request->getUri());
	}

	public function test_it_skips_null_parameters()
	{
		$request = new Request(Method::GET, '/Locator')
			->addParameter('Zone', '1000')
			->addParameter('Street', null);

		$this->assertSame('/Locator?Zone=1000', $request->getUri());
	}

	public function test_it_reports_whether_it_carries_a_body()
	{
		$request = new Request(Method::POST, '/orders');

		$this->assertFalse($request->hasBody());
		$this->assertTrue($request->withBody('<order/>')->hasBody());
		$this->assertFalse($request->withBody('')->hasBody());
	}

	public function test_when_applies_a_callback_conditionally()
	{
		$request = new Request(Method::GET, '/Locator')
			->when(true, fn (Request $request) => $request->addParameter('Info', '1'))
			->when(false, fn (Request $request) => $request->addParameter('CheckList', '1'));

		$this->assertSame('/Locator?Info=1', $request->getUri());
	}
}
