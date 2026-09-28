# bpost API client

## About

_bpost API library_ is a PHP client for the bpost APIs: the Shipping Manager, the Geolocator
parcel announcement and tracking.

Built against the *bpack integration manual* v3.3.35 and bpost's own SHM API v5 example set.
Upgrading from 3.x? See [MIGRATION.md](MIGRATION.md).

## Installation

```bash
composer require webatvantage/bpost-api-library
```

## Usage

Every bpost service is a separate API with its own host and credentials. Construct the one you
need, or the central client if you use more than one.

```php
use Webatvantage\Bpost\Api\BpostApiClient;
use Webatvantage\Bpost\Api\BpostApiConfig;
use Webatvantage\Bpost\Api\Shm\ShmApiConfig;
use Webatvantage\Bpost\Api\Geo\GeoApiConfig;
use Webatvantage\Bpost\Api\Parcel\ParcelApiConfig;

$bpost = new BpostApiClient(new BpostApiConfig(
    shm: new ShmApiConfig(accountId: '123456', passphrase: 'MyGreatApiPassword'),
    geo: new GeoApiConfig(partner: '123456', apiKey: 'xxxxxxxx'),
    parcel: new ParcelApiConfig(accountId: '123456', password: '...'),
));

$bpost->shm()->orders()->create($order);
$bpost->geo()->servicePoints()->nearest(zone: '1000')->get();
```

Reaching a service you did not configure throws `MissingConfigurationException` rather than failing
later at the HTTP layer.

### Shipping Manager

#### Building an order

```php
use Webatvantage\Bpost\Api\Enums\Language;
use Webatvantage\Bpost\Api\Shm\DataObjects\{Order, Box, Sender, Receiver, Address};
use Webatvantage\Bpost\Api\Shm\DataObjects\Box\AtHome;
use Webatvantage\Bpost\Api\Shm\DataObjects\Options\{Insured, Messaging};
use Webatvantage\Bpost\Api\Shm\Enums\{InsuranceAmount, Product};

$sender = new Sender()
    ->name('Business Solutions Team')
    ->company('bpost - bpack')
    ->address(
        new Address()
            ->streetName('Muntcentrum')->number(1)
            ->postalCode(1000)->locality('Brussel')->countryCode('BE'),
    )
    ->emailAddress('esolutions@bpost.be')
    ->phoneNumber('0032499123456');

$receiver = new Receiver()
    ->name('Alma van Appel')
    ->address(
        new Address()
            ->streetName('Rue du Grand Duc')->number(13)
            ->postalCode(1040)->locality('Etterbeek')->countryCode('BE'),
    )
    ->emailAddress('alma@example.com');

$order = new Order('ref_0123456789')
    ->costCenter('Webshop')
    ->addLine('Article description', 1)
    ->addBox(
        new Box()
            ->sender($sender)
            ->deliverTo(
                new AtHome(Product::Bpack24hPro)
                    ->weight(2000)
                    ->receiver($receiver)
                    ->withOption(Messaging::infoNextDay(Language::EN)->email('alma@example.com'))
                    ->withOption(Insured::additional(InsuranceAmount::UpTo2500)),
            )
            ->remark('Handle with care'),
    );
```

Other delivery methods take the place of `AtHome`: `AtBpost` for a pick-up point, `At247` for a
parcel locker, `International` for an address abroad and `AtIntlPugo` for a pick-up point abroad.

Lengths the manual documents are checked when you set them, so an over-long name throws
`InvalidLengthException` rather than coming back as a schema violation from bpost.

#### Orders

```php
use Webatvantage\Bpost\Api\Shm\Enums\BoxStatus;

$shm = $bpost->shm();

$shm->orders()->create($order);
$order = $shm->orders()->get('ref_0123456789');
$shm->orders()->updateStatus('ref_0123456789', BoxStatus::Open);

foreach ($order->boxes as $box) {
    $box->status;                 // BoxStatus
    $box->barcode;
    $box->deliveryBox->product;   // Product
}
```

#### Labels

```php
use Webatvantage\Bpost\Api\Shm\Enums\{LabelFormat, LabelOutput};

$labels = $shm->labels()->forOrder('ref_0123456789', LabelFormat::A6, LabelOutput::Pdf)->get();

foreach ($labels as $label) {
    file_put_contents($label->barcode() . '.pdf', $label->contents());
}

// One box again, by barcode
$labels = $shm->labels()->forBox($barcode)->get();

// Several orders at once, with return labels
$labels = $shm->labels()->inBulk(['ref_1', 'ref_2'], withReturnLabels: true)->get();

// ZPL, which bpost only produces in A6
$labels = $shm->labels()->forOrder('ref_1', LabelFormat::A6, LabelOutput::Zpl)->get();
```

#### Product configuration

What this account may actually sell. Worth reading before building an order, since bpost refuses a
product the account is not configured for.

```php
$configuration = $shm->productConfiguration()->get();

$configuration->offers(Product::Bpack24hPro);   // bool
```

### Parcel: announcement and tracking

The route for anyone printing their own labels. Announce the parcel before it reaches bpost, then
follow it afterwards.

```php
use Webatvantage\Bpost\Api\Parcel\DataObjects\{Announcement, Sender, Receiver, Address};
use Webatvantage\Bpost\Api\Parcel\DataObjects\Options\Flags\Signature;

$parcel = $bpost->parcel();

$feedback = $parcel->announcements()->create(
    new Announcement('323212345689100101119030', $sender, $receiver, weightInGrams: 250)
        ->customerReference('order-123')
        ->costCenter('Webshop')
        ->withOption(new Signature()),
);

if ($feedback->hasErrors()) {
    // A 201 does not mean bpost accepted it cleanly
    $feedback->errors;
}
```

```php
$tracking = $parcel->tracking()->get('323212345659900040669030');

$tracking->isDelivered();
$tracking->latestState()?->stateDescription;   // "DistributedNormally - regular"
$tracking->trackingUrl();                      // the page to show a customer
$tracking->pickupPoint?->name;                 // where it is waiting, if it is
```

This service spells addresses its own way — `houseNumber`, `boxNumber` and `city`, where the
Shipping Manager says `number`, `box` and `locality` — so it has its own `Address` class.

### Geolocator (pick-up points, parcel points and parcel lockers)

```php
use Webatvantage\Bpost\Api\Geo\GeoApiClient;
use Webatvantage\Bpost\Api\Geo\GeoApiConfig;
use Webatvantage\Bpost\Api\Geo\Enums\PointType;

$geo = new GeoApiClient(new GeoApiConfig(
    partner: '999999',      // your bpost account id, activated for the Geolocator
    apiKey: 'xxxxxxxx',     // the x-api-key bpost issues per account, request it from esolutions@bpost.be
    appId: 'A001',          // optional
));
```

#### Nearest points

```php
$points = $geo->servicePoints()
    ->nearest(zone: '1000', street: 'Grand Place', number: '3')
    ->types(PointType::PostOffice, PointType::PostPoint)
    ->language(Language::FR)
    ->withDetails()   // ask for opening hours
    ->limit(5)
    ->get();

foreach ($points as $point) {
    $point->name;
    $point->distance;               // metres
    $point->openingHours->for(Weekday::Monday)?->amOpen;
}
```

#### One point's details

```php
$point = $geo->servicePoints()->details('220000', PointType::PostOffice)->get();
```

#### Every point in a country

```php
$points = $geo->servicePoints()
    ->all()
    ->country('BE')
    ->type(PointType::ParcelLocker)
    ->get();
```

#### The URL of bpost's own details page

```php
$url = $geo->servicePoints()->pageUrl('220000', PointType::PostOffice);
```

## Would like contribute ?

You can read the [CONTRIBUTING.md](https://github.com/webatvantage/bpost-api-library/blob/main/CONTRIBUTING.md) file
