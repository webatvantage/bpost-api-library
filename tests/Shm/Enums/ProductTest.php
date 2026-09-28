<?php

namespace Webatvantage\Bpost\Api\Tests\Shm\Enums;

use Webatvantage\Bpost\Api\Shm\Enums\BoxStatus;
use Webatvantage\Bpost\Api\Shm\Enums\LabelFormat;
use Webatvantage\Bpost\Api\Shm\Enums\LabelOutput;
use Webatvantage\Bpost\Api\Shm\Enums\Product;
use Webatvantage\Bpost\Api\Tests\Shm\ShmTestCase;

class ProductTest extends ShmTestCase
{
	/**
	 * The casing is bpost's and is load-bearing, so it is worth pinning.
	 */
	public function test_product_names_keep_bposts_own_casing()
	{
		$this->assertSame('bpack 24h Pro', Product::Bpack24hPro->value);
		$this->assertSame('bpack 24h business', Product::Bpack24hBusiness->value);
		$this->assertSame('bpack 24/7', Product::Bpack247->value);
		$this->assertSame('bpack@bpost international', Product::BpackAtBpostInternational->value);
	}

	/**
	 * bpack XL was missing from 3.x entirely.
	 */
	public function test_bpack_xl_is_the_only_product_taking_dimensions_and_fragile()
	{
		$this->assertTrue(Product::BpackXL->requiresDimensions());
		$this->assertTrue(Product::BpackXL->allowsFragile());

		$this->assertFalse(Product::Bpack24hPro->requiresDimensions());
		$this->assertFalse(Product::Bpack24hPro->allowsFragile());
	}

	public function test_only_three_statuses_can_be_set_by_the_caller()
	{
		$settable = array_values(array_filter(BoxStatus::cases(), fn (BoxStatus $s) => $s->isSettable()));

		$this->assertSame([BoxStatus::Open, BoxStatus::Cancelled, BoxStatus::OnHold], $settable);
	}

	public function test_each_label_output_has_its_own_accept_type()
	{
		$this->assertSame('application/vnd.bpost.shm-label-pdf-v3.4+XML', LabelOutput::Pdf->acceptHeader());
		$this->assertSame('application/vnd.bpost.shm-label-image-v3.4+XML', LabelOutput::Png->acceptHeader());
		$this->assertSame('application/vnd.bpost.shm-label-zpl-v5+XML', LabelOutput::Zpl->acceptHeader());
	}

	public function test_zpl_is_only_produced_for_a6()
	{
		LabelOutput::Zpl->assertSupports(LabelFormat::A6);
		LabelOutput::Pdf->assertSupports(LabelFormat::A4);

		$this->expectException(\Webatvantage\Bpost\Api\Exceptions\InvalidValueException::class);

		LabelOutput::Zpl->assertSupports(LabelFormat::A4);
	}
}
