# bpost API client

[![Build Status](https://scrutinizer-ci.com/g/Antidot-be/bpost-api-library/badges/build.png?b=master)](https://scrutinizer-ci.com/g/Antidot-be/bpost-api-library)
[![Latest Stable Version](https://poser.pugx.org/antidot-be/bpost-api-library/v/stable)](https://packagist.org/packages/antidot-be/bpost-api-library)
[![Latest Unstable Version](https://poser.pugx.org/antidot-be/bpost-api-library/v/unstable)](https://packagist.org/packages/antidot-be/bpost-api-library)
[![Scrutinizer Quality Score](https://scrutinizer-ci.com/g/Antidot-be/bpost-api-library/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/Antidot-be/bpost-api-library)
[![Code Coverage](https://scrutinizer-ci.com/g/Antidot-be/bpost-api-library/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/Antidot-be/bpost-api-library)
[![Total Downloads](https://poser.pugx.org/antidot-be/bpost-api-library/downloads)](https://packagist.org/packages/antidot-be/bpost-api-library)
[![License](https://poser.pugx.org/antidot-be/bpost-api-library/license)](https://packagist.org/packages/antidot-be/bpost-api-library)

## About

_bpost API library_ is a PHP library which permit to your PHP application to communicate with the [bpost API](http://bpost.be).

## Installation

```bash
composer require webatvantage/bpost-api-library
```

## Usages

### Orders

#### Common objects

```php

/* Call the Composer autoloader */
require '../vendor/autoload.php';

use Bpost\BpostApiClient\Bpost;
use Bpost\BpostApiClient\Bpost\Order;
use Bpost\BpostApiClient\Bpost\Order\Address;
use Bpost\BpostApiClient\Bpost\Order\Box;
use Bpost\BpostApiClient\Bpost\Order\Box\AtBpost;
use Bpost\BpostApiClient\Bpost\Order\Box\AtHome;
use Bpost\BpostApiClient\Bpost\Order\Box\CustomsInfo\CustomsInfo;
use Bpost\BpostApiClient\Bpost\Order\Box\International;
use Bpost\BpostApiClient\Bpost\Order\Box\Option\Insured;
use Bpost\BpostApiClient\Bpost\Order\Box\Option\Messaging;
use Bpost\BpostApiClient\Bpost\Order\Line;
use Bpost\BpostApiClient\Bpost\Order\PugoAddress;
use Bpost\BpostApiClient\Bpost\Order\Receiver;
use Bpost\BpostApiClient\Bpost\ProductConfiguration\Product;
use Bpost\BpostApiClient\BpostException;
use Psr\Log\LoggerInterface;


$apiUrl = "https://api-parcel.bpost.be/services/shm/";
$apiUsername = "107423";
$apiPassword = "MyGreatApiPassword";

$bpost = new Bpost($apiUsername, $apiPassword, $apiUrl);

/* We set the receiver postal address, without the name */
$receiverAddress = new Address();
$receiverAddress->setStreetName("Rue du Grand Duc");
$receiverAddress->setNumber(13);
$receiverAddress->setPostalCode(1040);
$receiverAddress->setLocality("Etterbeek");
$receiverAddress->setCountryCode("BE"); // ISO2

/* We set the receiver postal address, without the name */
$receiver = new Receiver();
$receiver->setAddress($receiverAddress);
$receiver->setName("Alma van Appel");
$receiver->setPhoneNumber("+32 2 641 13 90");
$receiver->setEmailAddress("alma@antidot.com");

$orderReference = "ref_0123456789"; // An unique order reference
$order = new Order($orderReference);

/**
 * A order line is an order item, like a article
 */
$order->addLine(
    new Line("Article description", 1)
);
$order->addLine(
    new Line("Some others articles", 5)
);

/**
 * A box is used to split your shipping in many packages
 * The box weight must be littlest than to 30kg
 */
$box = new Box();

/**
 * Available boxes for national box:
 * - AtHome: Delivered at the given address
 * - AtBpost: Delivered in a bpost office
 * - BpostOnAppointment: Delivered in a shop
 *
 * Available boxes for international box:
 * - International: Delivered at the given address
 */
$atHome = new AtHome();
$atHome->setProduct(Product::PRODUCT_NAME_BPACK_24H_BUSINESS);
$atHome->setReceiver($receiver);

/* Add options */
$atHome->addOption(
    new Insured(
        Insured::INSURANCE_TYPE_ADDITIONAL_INSURANCE,
        Insured::INSURANCE_AMOUNT_UP_TO_2500_EUROS
    )
);

$box->setNationalBox($atHome);

$order->addBox($box);
```

#### Create an order

We use the variables set before.

```php
$bpost->createOrReplaceOrder($order); // The order is created with status Box::BOX_STATUS_PENDING
```

#### Update order status

```php
$bpost->modifyOrderStatus($orderReference, Box::BOX_STATUS_OPEN);
```

#### Get order info

```php
$order = $bpost->fetchOrder($orderReference);

$boxes = $order->getBoxes();
$lines = $order->getLines();
```

### Labels

#### Get labels from an order

```php
$labels = $bpost->createLabelForOrder(
    $orderReference,
    Bpost::LABEL_FORMAT_A6, // $format
    false, // $withReturnLabels
    true // $asPdf
);
foreach ($labels as $label) {
    $barcode = $label->getBarcode();
    $mimeType = $label->getMimeType(); // Label::LABEL_MIME_TYPE_*
    $bytes = $label->getBytes();
    file_put_contents("$barcode.pdf", $bytes);
}
```

#### Get labels from an existing barcode

```php
$labels = $bpost->createLabelForOrder(
    $boxBarcode,
    Bpost::LABEL_FORMAT_A6, // $format
    false, // $withReturnLabels
    true // $asPdf
);
foreach ($labels as $label) {
    $barcode = $label->getBarcode(); // Can be different than $boxBarcode if this is a return label
    $mimeType = $label->getMimeType(); // Label::LABEL_MIME_TYPE_*
    $bytes = $label->getBytes();
    file_put_contents("$barcode.pdf", $bytes);
}
```

#### Get labels from an existing barcode

```php
$labels = $bpost->createLabelInBulkForOrders(
    array(
        $orderReference1,
        $orderReference2,
    ),
    Bpost::LABEL_FORMAT_A6, // $format
    false, // $withReturnLabels
    true // $asPdf
);
foreach ($labels as $label) {
    $barcode = $label->getBarcode(); // Can be different than $boxBarcode if this is a return label
    $mimeType = $label->getMimeType(); // Label::LABEL_MIME_TYPE_*
    $bytes = $label->getBytes();
    file_put_contents("$barcode.pdf", $bytes);
}
```

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

## Sites using this class

* [Each site based on the Web Retail Shop Platform](http://www.webretailcompany.be)
* [The bpost plugin for WordPress](https://wordpress.org/plugins/bpost-shipping)

## Would like contribute ?

You can read the [CONTRIBUTING.md](https://github.com/Antidot-be/bpost-api-library/blob/master/CONTRIBUTING.md) file
