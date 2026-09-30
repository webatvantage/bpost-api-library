# bpost API client

## About

_bpost API library_ is a PHP client for the bpost APIs: the Shipping Manager, the Geolocator,
parcel announcement and tracking.

Built against the *bpack integration manual* v3.3.35 and bpost's own SHM API v5 example set.
Upgrading from 1.x? See [MIGRATION.md](MIGRATION.md).

## Requirements

PHP 8.5 or newer.

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

### Logging

Pass a PSR-3 logger and every request and response is written to it. You can then narrow that down
at three levels: the whole client, one service, or one call.

```php
$bpost = new BpostApiClient($config, logger: $logger);

// Everything, including services you have not reached yet.
$bpost->withoutLogging();

// One service.
$bpost->shm()->withoutLogging();

// One call. A resource is built fresh each time, so this reaches nothing else.
$bpost->shm()->orders()->withoutLogging()->get('order-123');

// Or on a request you were narrowing anyway.
$bpost->geo()->servicePoints()->nearest(zone: '1000')->withoutLogging()->get();
```

Each of these has a `withLogging(bool $logging = true)` counterpart, so a single noisy call can be
logged while the rest of the client stays quiet. The narrowest setting wins.

### Debugging

When bpost refuses a document, the body is usually the only thing that says why. `withDebug()`
hands you the PSR-7 request and response themselves, at the same four levels:

```php
$seen = function (RequestInterface $request, ResponseInterface $response) {
    echo (string) $request->getBody(), (string) $response->getBody();
};

$bpost->withDebug($seen);                                  // every service
$bpost->shm()->withDebug($seen);                           // one service
$bpost->shm()->orders()->withDebug($seen)->get('order-1'); // one resource
$bpost->geo()->servicePoints()->all()->withDebug($seen)->get();
```

Pass `null` to clear it. The callback runs whether or not the response was an error, and before
the exception is raised, so it sees the body of a refused request too.

Both ladders are interfaces — `Contracts\Loggable` and `Contracts\Debuggable` — so every level
spells them the same way.

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
`InvalidLengthException` rather than coming back as a schema violation from bpost. An email
address is also checked for shape and throws `InvalidPatternException`, since bpost accepts a
malformed one and then silently never sends the message. Reading is not held to either: an order
bpost already holds comes back as it is, however far outside the documented limits it falls.

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

This service has its own `Sender`, `Receiver` and `Address` — they are not the Shipping Manager's
and cannot be swapped for them.

```php
use Webatvantage\Bpost\Api\Parcel\DataObjects\{Announcement, Sender, Receiver, Address, ContactDetail};
use Webatvantage\Bpost\Api\Parcel\DataObjects\Options\Flags\Signature;

$parcel = $bpost->parcel();

$sender = new Sender()
    ->name('bpost - bpack')
    ->address(
        new Address()
            ->streetName('Muntcentrum')->houseNumber(1)
            ->postalCode(1000)->city('Brussel')->countryCode('BE'),
    )
    ->contactDetail(new ContactDetail()->emailAddress('esolutions@bpost.be'));

$receiver = new Receiver()
    ->name('Alma van Appel')
    ->address(
        new Address()
            ->streetName('Rue du Grand Duc')->houseNumber(13)
            ->postalCode(1040)->city('Etterbeek')->countryCode('BE'),
    )
    ->contactDetail(new ContactDetail()->emailAddress('alma@example.com'));

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

It spells the address fields its own way too — `houseNumber`, `boxNumber` and `city`, where the
Shipping Manager says `number`, `box` and `locality` — and puts the email and phone in a
`ContactDetail` rather than on the party itself.

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

#### Which host, and whether you need a key

The key goes with the host. `pudo.bpost.cloud`, the default since 2.0 and the one bpost documents,
rejects a request without `x-api-key`. The older `pudo.bpost.be` still answers and ignores the
header entirely, so an integration pointed at it needs no key:

```php
$geo = new GeoApiClient(new GeoApiConfig(
    partner: '999999',
    apiKey: '',                            // unused on this host
    baseUri: 'https://pudo.bpost.be',
));
```

`GeoApiConfig` asks for the key whichever host you name, because the default is the one that
requires it — pass an empty string on the legacy host rather than leaving the argument out.

#### Nearest points

```php
use Webatvantage\Bpost\Api\Enums\Language;
use Webatvantage\Bpost\Api\Enums\Weekday;

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

The Geolocator only offers `NL` and `FR`; `Language` carries `EN` and `DE` for the Shipping
Manager's messaging, and passing either here throws `InvalidValueException` rather than being
ignored on bpost's side.

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

## Contributing

You can read the [CONTRIBUTING.md](https://github.com/webatvantage/bpost-api-library/blob/main/CONTRIBUTING.md) file
