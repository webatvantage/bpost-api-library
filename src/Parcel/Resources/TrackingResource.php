<?php

namespace Webatvantage\Bpost\Api\Parcel\Resources;

use Webatvantage\Bpost\Api\Contracts\Resource;
use Webatvantage\Bpost\Api\Parcel\DataObjects\ItemTracking;
use Webatvantage\Bpost\Api\Parcel\Requests\FetchTrackingInfoRequest;

class TrackingResource extends Resource
{
	public function get(string $barcode): ItemTracking
	{
		return $this->prepare(new FetchTrackingInfoRequest($this->apiAdapter, $barcode))->get();
	}
}
