# Migrating from 3.x to 4.0

Version 4.0 splits the library into one namespace per bpost service and replaces the hand-rolled
cURL transport with Guzzle. Every class moved, so this is a hard break — there are no aliases.

Work through it in three steps: swap the entry point, update the `use` statements from the table,
then check the removals at the bottom.

## 1. Entry point

Every service is reached from one client.

```php
use Webatvantage\Bpost\Api\BpostApiClient;
use Webatvantage\Bpost\Api\BpostApiConfig;

$bpost = new BpostApiClient(new BpostApiConfig(
    shm:    new ShmApiConfig(accountId: '123456', passphrase: '...'),
    geo:    new GeoApiConfig(partner: '123456', apiKey: '...'),
    parcel: new ParcelApiConfig(login: '123456', password: '...'),
));
```

Each service can also be constructed on its own if you only use one — `new ShmApiClient($config)`.

## 2. Class map

Filled in per phase as classes move.

| 3.x | 4.0 |
|---|---|
| `Bpost\BpostApiClient\Geo6` | `Webatvantage\Bpost\Api\Geo\GeoApiClient` |
| `Bpost\BpostApiClient\Geo6\Geo6Partner` | `Webatvantage\Bpost\Api\Geo\GeoApiClient` |
| `Bpost\BpostApiClient\Geo6\Geo6Account` | `Webatvantage\Bpost\Api\Geo\GeoApiClient` |
| `Bpost\BpostApiClient\Geo6\Poi` | `Webatvantage\Bpost\Api\Geo\DataObjects\ServicePoint` |
| `Bpost\BpostApiClient\Geo6\Day` | `Webatvantage\Bpost\Api\Geo\DataObjects\Day` |
| `Bpost\BpostApiClient\Geo6\Service` | `Webatvantage\Bpost\Api\Geo\DataObjects\Service` |
| `Geo6::POINT_TYPE_*` | `Webatvantage\Bpost\Api\Geo\Enums\PointType` |
| `BpostTaxipostLocatorException` | `Webatvantage\Bpost\Api\Geo\Exceptions\LocatorException` |
| `BpostInvalidDayException` | `Webatvantage\Bpost\Api\Exceptions\InvalidValueException` |
| _(Phase 2 — Shipping Manager)_ | |
| _(Phase 3 — Parcel)_ | |

## 3. Removed with no replacement

Filled in per phase.

| Removed | Why | What to do |
|---|---|---|
| `Geo6::getServicePointPage()` | Deprecated alias of `getServicePointPageUrl()` | `$geo->servicePoints()->pageUrl($id, $type)` |
| `Geo6::getPointType()` | Replaced by a real enum | `PointType::mask(PointType::PostOffice, ...)` |
| `Geo6::setTimeOut()` / `setUserAgent()` | Guzzle options replace them | Pass `['timeout' => 10]` as `$httpClientOptions` |

### Geolocator, in detail

The three service-point calls now return the same `ServicePoint` object, and the nearest-points
search returns a flat list rather than `['poi' => ..., 'distance' => ...]` pairs — the distance is
a property on the point.

```php
// 3.x
$geo6 = new Geo6\Geo6Partner('999999', 'A001');
foreach ($geo6->getNearestServicePoint('Grand Place', '3', '1000') as $item) {
    $item['poi']->getOffice();
    $item['distance'];
}

// 4.0
$geo = new GeoApiClient(new GeoApiConfig(partner: '999999', apiKey: 'xxxx', appId: 'A001'));
foreach ($geo->servicePoints()->nearest(zone: '1000', street: 'Grand Place', number: '3')->get() as $point) {
    $point->name;
    $point->distance;
}
```

An `apiKey` is now required: bpost made `x-api-key` mandatory on the Geolocator domain. Request one
from esolutions@bpost.be with your account id.

Opening hours only arrive when you ask for them with `->withDetails()`, which sends `Info=1`. The
3.x client never sent it, so `Poi::getHours()` was always empty.
