<?php

namespace Webatvantage\Bpost\Api\Tests\Shm\Requests;

use Webatvantage\Bpost\Api\Shm\DataObjects\Box;
use Webatvantage\Bpost\Api\Shm\DataObjects\Box\AtHome;
use Webatvantage\Bpost\Api\Shm\DataObjects\Order;
use Webatvantage\Bpost\Api\Shm\Enums\BoxStatus;
use Webatvantage\Bpost\Api\Shm\Enums\LabelFormat;
use Webatvantage\Bpost\Api\Shm\Enums\LabelOutput;
use Webatvantage\Bpost\Api\Shm\Enums\Product;
use Webatvantage\Bpost\Api\Shm\ShmApiClient;
use Webatvantage\Bpost\Api\Tests\Shm\ShmTestCase;

class ShmRequestTest extends ShmTestCase
{
	private function client(): ShmApiClient
	{
		return new ShmApiClient($this->config(), ['handler' => $this->handlerStack()]);
	}

	private function order(): Order
	{
		return new Order('ref-123')->addBox(new Box()->deliverTo(new AtHome(Product::Bpack24hPro)->weight(2000)));
	}

	public function test_creating_an_order_posts_to_the_account_path()
	{
		$this->mockResponse(201, '');

		$this->client()->orders()->create($this->order());

		$request = $this->lastRequest();
		$this->assertSame('POST', $request->getMethod());
		$this->assertSame('https://shm-rest.bpost.cloud/services/shm/123456/orders', (string)$request->getUri());
		$this->assertStringContainsString('<tns:order', (string)$request->getBody());
	}

	public function test_creating_an_order_sends_the_v5_order_media_type()
	{
		$this->mockResponse(201, '');

		$this->client()->orders()->create($this->order());

		$this->assertSame(
			'application/vnd.bpost.shm-order-v5+XML',
			$this->lastRequest()->getHeaderLine('Content-Type'),
		);
	}

	public function test_it_authenticates_with_the_account_and_passphrase()
	{
		$this->mockResponse(201, '');

		$this->client()->orders()->create($this->order());

		$this->assertSame(
			'Basic ' . base64_encode('123456:passphrase'),
			$this->lastRequest()->getHeaderLine('Authorization'),
		);
	}

	public function test_fetching_an_order_accepts_the_v3_5_media_type()
	{
		$this->mockResponse(200, '<orderInfo><reference>ref-123</reference></orderInfo>');

		$order = $this->client()->orders()->get('ref-123');

		$this->assertSame('ref-123', $order->reference);
		$this->assertSame('GET', $this->lastRequest()->getMethod());
		$this->assertSame(
			'application/vnd.bpost.shm-order-v3.5+XML',
			$this->lastRequest()->getHeaderLine('Accept'),
		);
	}

	public function test_updating_a_status_posts_the_v3_order_update_payload()
	{
		$this->mockResponse(200, '');

		$this->client()->orders()->updateStatus('ref-123', BoxStatus::Open);

		$request = $this->lastRequest();
		$this->assertSame('POST', $request->getMethod());
		$this->assertSame(
			'https://shm-rest.bpost.cloud/services/shm/123456/orders/ref-123',
			(string)$request->getUri(),
		);
		$this->assertSame(
			'application/vnd.bpost.shm-orderUpdate-v3+XML',
			$request->getHeaderLine('Content-Type'),
		);
		$this->assertStringContainsString('<status>OPEN</status>', (string)$request->getBody());
	}

	public function test_it_refuses_a_status_only_bpost_may_set()
	{
		$this->expectException(\Webatvantage\Bpost\Api\Exceptions\InvalidValueException::class);

		$this->client()->orders()->updateStatus('ref-123', BoxStatus::Delivered);
	}

	/**
	 * 3.x sent labelRequest-v3 here, which both the manual and every v5 example contradict. The
	 * Accept type genuinely does stay at v3.4 — bpost did not bump the two together.
	 */
	public function test_a_label_request_sends_the_v5_label_request_media_type()
	{
		$this->mockResponse(200, '<labels/>');

		$this->client()->labels()->forOrder('ref-123')->get();

		$request = $this->lastRequest();
		$this->assertSame(
			'application/vnd.bpost.shm-labelRequest-v5+XML',
			$request->getHeaderLine('Content-Type'),
		);
		$this->assertSame(
			'application/vnd.bpost.shm-label-pdf-v3.4+XML',
			$request->getHeaderLine('Accept'),
		);
	}

	public function test_label_paths_match_the_annex()
	{
		$this->mockResponse(200, '<labels/>');
		$this->client()->labels()->forOrder('ref-123', LabelFormat::A4)->get();
		$this->assertSame(
			'https://shm-rest.bpost.cloud/services/shm/123456/orders/ref-123/labels/A4',
			(string)$this->lastRequest()->getUri(),
		);

		$this->mockResponse(200, '<labels/>');
		$this->client()->labels()->forBox('323212345', LabelFormat::A6, withReturnLabels: true)->get();
		$this->assertSame(
			'https://shm-rest.bpost.cloud/services/shm/123456/boxes/323212345/labels/A6/withReturnLabels',
			(string)$this->lastRequest()->getUri(),
		);

		$this->mockResponse(200, '<labels/>');
		$this->client()->labels()->inBulk(['a', 'b'])->get();
		$this->assertSame(
			'https://shm-rest.bpost.cloud/services/shm/123456/labels/A6',
			(string)$this->lastRequest()->getUri(),
		);
		$this->assertStringContainsString('<order>a</order>', (string)$this->lastRequest()->getBody());
	}

	/**
	 * ZPL was missing from 3.x entirely, and bpost only produces it for A6.
	 */
	public function test_zpl_is_requested_with_its_own_accept_type()
	{
		$this->mockResponse(200, '<labels/>');

		$this->client()->labels()->forOrder('ref-123', LabelFormat::A6, LabelOutput::Zpl)->get();

		$this->assertSame(
			'application/vnd.bpost.shm-label-zpl-v5+XML',
			$this->lastRequest()->getHeaderLine('Accept'),
		);
	}

	public function test_zpl_is_refused_for_a4()
	{
		$this->expectException(\Webatvantage\Bpost\Api\Exceptions\InvalidValueException::class);

		$this->client()->labels()->forOrder('ref-123', LabelFormat::A4, LabelOutput::Zpl);
	}

	public function test_it_reads_labels_out_of_the_response()
	{
		$this->mockResponse(200, <<<'XML'
			<labels>
				<label>
					<barcodeWithReference>
						<barcode>323299901059912015292030</barcode>
						<reference>ref-123</reference>
					</barcodeWithReference>
					<barcodeWithReference>
						<barcode>323299901059912015293050</barcode>
						<reference>ref-123</reference>
					</barcodeWithReference>
					<mimeType>application/pdf</mimeType>
					<bytes>SGVsbG8=</bytes>
				</label>
			</labels>
			XML);

		$labels = $this->client()->labels()->forOrder('ref-123', withReturnLabels: true)->get();

		$this->assertCount(1, $labels);
		$this->assertSame('323299901059912015292030', $labels[0]->barcode());
		$this->assertSame('application/pdf', $labels[0]->mimeType);
		$this->assertSame('Hello', $labels[0]->contents());
		$this->assertFalse($labels[0]->barcodes[0]->isReturnLabel());
		$this->assertTrue($labels[0]->barcodes[1]->isReturnLabel());
	}

	public function test_the_product_configuration_accepts_its_own_media_type()
	{
		$this->mockResponse(200, '<productConfiguration><deliveryMethod name="home or office"/></productConfiguration>');

		$configuration = $this->client()->productConfiguration()->get();

		$this->assertCount(1, $configuration->deliveryMethods);
		$this->assertSame(
			'https://shm-rest.bpost.cloud/services/shm/123456/productconfig',
			(string)$this->lastRequest()->getUri(),
		);
		$this->assertSame(
			'application/vnd.bpost.shm-productConfiguration-v3.1+XML',
			$this->lastRequest()->getHeaderLine('Accept'),
		);
	}
}
