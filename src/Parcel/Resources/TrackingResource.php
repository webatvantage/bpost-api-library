<?php

namespace Webatvantage\Bpost\Api\Parcel\Resources;

use Webatvantage\Bpost\Api\Parcel\DataObjects\ItemTracking;
use Webatvantage\Bpost\Api\Parcel\Requests\FetchTrackingInfoRequest;
use Webatvantage\Bpost\Api\Resources\Resource;

class TrackingResource extends Resource
{
	public function get(string $barcode): ItemTracking
	{
		return new FetchTrackingInfoRequest($this->apiAdapter, $barcode)->get();
	}
}
