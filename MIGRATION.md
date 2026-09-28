# Migrating from 1.x to 2.0

Version 2.0 splits the library into one namespace per bpost service and replaces the hand-rolled
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

| 1.x | 2.0 |
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
| `Bpost\BpostApiClient\Bpost` | `Webatvantage\Bpost\Api\Shm\ShmApiClient` |
| `…\Bpost\Order` | `…\Shm\DataObjects\Order` |
| `…\Bpost\Order\Line` | `…\Shm\DataObjects\OrderLine` |
| `…\Bpost\Order\Box` | `…\Shm\DataObjects\Box` |
| `…\Bpost\Order\{Address,Sender,Receiver,PugoAddress,ParcelsDepotAddress}` | `…\Shm\DataObjects\…` |
| `…\Bpost\Order\Box\{AtHome,AtBpost,At247,International,AtIntlPugo}` | `…\Shm\DataObjects\Box\…` |
| `…\Bpost\Order\Box\National\Unregistered` | `…\Shm\DataObjects\Box\Unregistered` |
| `…\Bpost\Order\Box\Option\{Messaging,CashOnDelivery,Insured}` | `…\Shm\DataObjects\Options\…` |
| `…\Bpost\Order\Box\Option\{Signed,SaturdayDelivery,AutomaticSecondPresentation}` | `…\Shm\DataObjects\Options\Flags\…` |
| `…\Bpost\Order\Box\CustomsInfo\CustomsInfo` | `…\Shm\DataObjects\Customs\CustomsInfo` |
| `…\Bpost\Order\Box\International\ParcelContent` | `…\Shm\DataObjects\Customs\ParcelContent` |
| `…\Bpost\{Label,Labels}` | `…\Shm\DataObjects\Label` |
| `…\Bpost\Label\Barcode` | `…\Shm\DataObjects\Barcode` |
| `…\Bpost\ProductConfiguration*` | `…\Shm\DataObjects\ProductConfiguration\…` |
| `Product::PRODUCT_NAME_*` | `…\Shm\Enums\Product` |
| `Box::BOX_STATUS_*` | `…\Shm\Enums\BoxStatus` |
| `Bpost::LABEL_FORMAT_*` | `…\Shm\Enums\LabelFormat` |
| `Insured::INSURANCE_*` | `…\Shm\Enums\{InsuranceType,InsuranceAmount}` |
| `BpostException` and the `Exception\*` tree | `…\Exceptions\*` |
| _(new in 2.0 — no 1.x equivalent)_ | `Webatvantage\Bpost\Api\Parcel\ParcelApiClient` |

## 3. Removed with no replacement

Filled in per phase.

| Removed | Why | What to do |
|---|---|---|
| `Geo6::getServicePointPage()` | Deprecated alias of `getServicePointPageUrl()` | `$geo->servicePoints()->pageUrl($id, $type)` |
| `Geo6::getPointType()` | Replaced by a real enum | `PointType::mask(PointType::PostOffice, ...)` |
| `Geo6::setTimeOut()` / `setUserAgent()` | Guzzle options replace them | Pass `['timeout' => 10]` as `$httpClientOptions` |
| `FormHandler` | Not an API client: it built parameters for the Shipping Manager JavaScript widget and made no HTTP call. Its checksum was wrong anyway, hashing an `action` field the manual does not list | See the snippet below |
| `Bpack247` and `Bpack247\*` | Undocumented service on a dead host, reached over plain HTTP with Basic credentials | none |
| `Option\Insurance`, `Option\Signature` | Deprecated aliases since 3.5 | `Insured`, `Signed` |
| `Box\AtIntlHome` | Never functional: it inherited `International`'s parser, which reads a different element | `International` |
| `ProductConfiguration\Visibility` | Unreferenced, and its two values contradicted the ones on `DeliveryMethod` | `Shm\Enums\Visibility` |
| `BpostOnAppointment` | Appears nowhere in the v3.3.35 manual, and its parser dropped product, options, weight and opening hours | none — ask bpost if you need it |
| `Insured::INSURANCE_AMOUNT_UP_TO_7500_EUROS` … `_25000_EUROS` | bpost capped additional warranty at 5 000 EUR in 3.3.24; the library's own validation had rejected these ever since | `InsuranceAmount::UpTo2500`, `UpTo5000` |

### Replacing FormHandler

```php
$checksum = hash('sha256', implode('&', [
    'accountId=' . $accountId,
    'costCenter=' . $costCenter,
    'customerCountry=' . $countryCode,
    'extraSecure=',
    'orderReference=' . $reference,
]) . '&' . $passphrase);
```

The fields are the ones the manual lists in B.2.2.2, in alphabetical order, with the passphrase
appended after a final ampersand.

### Shipping Manager, in detail

```php
// 1.x
$bpost = new Bpost('123456', 'passphrase', 'https://api-parcel.bpost.be/services/shm/');
$order = new Order('ref-123');
$box = new Box();
$atHome = new AtHome();
$atHome->setProduct(Product::PRODUCT_NAME_BPACK_24H_PRO);
$atHome->setReceiver($receiver);
$atHome->addOption(new Insured(Insured::INSURANCE_TYPE_ADDITIONAL_INSURANCE, 2));
$box->setNationalBox($atHome);
$order->addBox($box);
$bpost->createOrReplaceOrder($order);
$labels = $bpost->createLabelForOrder('ref-123', Bpost::LABEL_FORMAT_A6, false, true);

// 2.0
$shm = new ShmApiClient(new ShmApiConfig(accountId: '123456', passphrase: 'passphrase'));
$shm->orders()->create(
    new Order('ref-123')->addBox(
        new Box()->sender($sender)->deliverTo(
            new AtHome(Product::Bpack24hPro)
                ->weight(2000)
                ->receiver($receiver)
                ->withOption(Insured::additional(InsuranceAmount::UpTo2500)),
        ),
    ),
);
$labels = $shm->labels()->forOrder('ref-123', LabelFormat::A6, LabelOutput::Pdf)->get();
```

Setters lost their `set` prefix and return `$this`, so an order reads as one expression. Values are
read as properties — `$box->deliveryBox->weight` — rather than through getters.

Lengths the manual documents are now checked when you set them rather than by bpost when you send:
sender and receiver name and company at 40, remark, order reference and cost centre at 50, and an
ordinary parcel over 30 kg — bpack XL and bpack Pallet have no documented ceiling and are not
capped. Code that previously sent an over-long value and got a schema violation back will now get
an `InvalidLengthException` at the point of the mistake.

`bpack XL` exists, with `Dimensions` and the `Fragile` option. Labels can be asked for as ZPL,
which bpost only produces in A6.

### Parcel, new in 2.0

Neither the Announcement nor the Tracking API existed in 1.x, so there is nothing to migrate —
but both are documented in the manual and are what you want if you print your own labels.

```php
$parcel = new ParcelApiClient(new ParcelApiConfig(accountId: '123456', password: '...'));

$feedback = $parcel->announcements()->create(
    new Announcement($barcode, $sender, $receiver, weightInGrams: 250)
        ->customerReference('order-123')
        ->withOption(new Signature()),
);

$feedback->hasErrors();   // a 201 does not mean bpost accepted it cleanly

$tracking = $parcel->tracking()->get($barcode);
$tracking->latestState()?->stateDescription;
$tracking->trackingUrl();
```

Its credentials are issued separately from the Shipping Manager passphrase — ask
esolutions@bpost.be. Note this service spells addresses its own way: `houseNumber`, `boxNumber` and
`city` where the Shipping Manager says `number`, `box` and `locality`, which is why
`Parcel\DataObjects\Address` is a different class from `Shm\DataObjects\Address`.

### Geolocator, in detail

The three service-point calls now return the same `ServicePoint` object, and the nearest-points
search returns a flat list rather than `['poi' => ..., 'distance' => ...]` pairs — the distance is
a property on the point.

```php
// 1.x
$geo6 = new Geo6\Geo6Partner('999999', 'A001');
foreach ($geo6->getNearestServicePoint('Grand Place', '3', '1000') as $item) {
    $item['poi']->getOffice();
    $item['distance'];
}

// 2.0
$geo = new GeoApiClient(new GeoApiConfig(partner: '999999', apiKey: 'xxxx', appId: 'A001'));
foreach ($geo->servicePoints()->nearest(zone: '1000', street: 'Grand Place', number: '3')->get() as $point) {
    $point->name;
    $point->distance;
}
```

An `apiKey` is now required: bpost made `x-api-key` mandatory on the Geolocator domain. Request one
from esolutions@bpost.be with your account id.

Opening hours only arrive when you ask for them with `->withDetails()`, which sends `Info=1`. The
1.x client never sent it, so `Poi::getHours()` was always empty.
