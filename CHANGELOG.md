# Changelog

### Unreleased

#### Fixed

* `atIntlPugo` wrote `<pugoAddress>` in the national namespace, where manual B.3.2.2.2 has
  `<international:pugoAddress>`, so every `bpack@bpost international` order carrying a pick-up
  point address failed bpost's schema validation. `Shm\DataObjects\Address::toXml()` took a
  namespace and ignored it; it now uses the one it is given and falls back to its own
* Passing a `handler` in `$httpClientOptions` had the log middleware pushed onto that stack once
  per service, so a client reaching all three bpost services wrote every request to the log three
  times over. The stack is cloned before the middleware goes on
* Reading a response no longer applies the send-side field lengths and ranges: a retrieved order
  whose locality runs past the documented 40 characters, or a parcel content weighing less than
  the minimum a new one may declare, is reported as bpost holds it instead of throwing
  `InvalidLengthException` at a caller with nothing to correct. `Validate::ignoring()` covers the
  reads whose checks sit in a constructor
* A malformed `deliveryTime` or scan `time` in a tracking response threw
  `DateMalformedStringException`, outside `BpostException`. It is an `UnexpectedValueException`
  now, and dates are read through the new `XmlElement::dateTime()`
* `Resource::debug()` set its callback on the shared adapter, where it stayed for the life of the
  client and fired for every later call on every other resource. It now reaches only the calls
  made on that resource, matching `withoutLogging()`
* The Geolocator's `language()` refused nothing, though all four operations document `NL` or `FR`
  only and quietly ignore anything else. `EN` and `DE` are rejected, in one place rather than four
* The readme's announcement example passed the Shipping Manager's `Sender` and `Receiver` to a
  service that has its own, so copying it raised a `TypeError`. Every block in the file is now run
  against the library rather than read over
* A price band quoted as `0` came back as `null`, the same answer as a band bpost did not quote
* `ApiExceptionFactory` and `Feedback` passed a context element to XPath and then opened with
  `//`, which resolves from the document root regardless
* `ServicePoint::withPageUrl()` assigned to the point it was called on and returned it, so a
  caller holding the original found the URL on that too. It hands back a copy now
* `Validate::countryCode()` counted characters rather than requiring letters, so `12` passed
* `Signed`'s docblock lost its leading asterisk on a blank line, which ended the comment early
* Five exceptions reported `statusCode` 200 for a response they had no status for, most often a
  2xx with an empty body where 204 is the likelier answer. They leave it unset now; a zero means
  the library could not read a body bpost had already accepted, rather than bpost refusing the
  request

#### Added

* `withDebug()` on `BpostApiClient` and on each service client, completing the same levels logging
  already had: the whole client, one service, one resource or one call
* `Contracts\Loggable` and `Contracts\Debuggable`, implemented at each of those levels. The names
  had drifted apart once already, and an interface makes that a load-time error rather than
  something to notice in review
* `Language::forGeolocator()`, the two of the four that service takes. The set was buried in the
  check that enforces it, so a caller building a language choice had to hardcode it and find out
  from a rejected value when it was wrong

#### Changed

* Log records carry the severity of the response: the request and the transfer statistics stay at
  `debug`, a 2xx is `info`, a 3xx `notice`, a 4xx `error` and a 5xx `critical`. Everything went out
  at `debug` before, so a refused order read the same as a successful one and keeping only the
  failures meant filtering on message text. A logger set to `warning` now does it
* Every `emailAddress` is checked with `filter_var` when set, not only for the documented length.
  bpost answers a malformed address by accepting the order and then never sending the message, so
  a typo used to surface as a customer asking where their parcel was. Two consequences worth
  knowing: an empty string is now refused where it used to be accepted and silently left out of
  the document, and `FILTER_VALIDATE_EMAIL` is stricter than RFC 5322, so an internationalised
  domain has to be punycoded before it is set. A response is read as bpost holds it, as ever
* `HttpApiAdapter::setLogging()` is now `withLogging()` and gained `withoutLogging()`;
  `setDebugCallback()` is now `withDebug()`. Every level of the ladder spells the pair the same way
* `.claude/` and `composer.lock` are `export-ignore`d, so a `composer require` no longer pulls
  down the repository's own working notes
* The Geolocator's two locker filters moved beside its language parameter, so a search and an
  all-points download cannot drift apart on what they send
* Static analysis runs at PHPStan level 8. Everything between five and eight was one cause:
  `Dom\Node::$textContent` is nullable and seven places trimmed it directly. `XmlElement` grew
  `ownText()` for the five that hold one, which is the reader `text()` already wanted
* `GeoApiConfig::$apiKey` is optional. `pudo.bpost.cloud` rejects a request without `x-api-key`,
  but the older `pudo.bpost.be` ignores the header, so an integration still pointed there had no
  key to pass and had to invent one. Left out, the header is not sent at all rather than sent
  empty; on the default host that turns a missing key into a rejected request rather than a
  missing argument

#### Removed

* `Enums\MediaType`, `Conditionable::unless()`, `Weekday::index()`, `Request::addHeader()`,
  `HttpApiAdapter::isLogging()`, and `toXml()`/`fromXml()` on the Shipping Manager's `Dimensions`.
  Nothing in the library reached any of them; `Dimensions` writes its three elements through
  `appendTo()`, because bpost has no wrapper element for them

### 2.0.0 - 2026-09-28

#### Added

* Guzzle-based `HttpApiAdapter` shared by every bpost service, replacing the two hand-rolled cURL
  blocks and the `ApiCaller` wrapper
* `MIGRATION.md`, a 1.x to 2.0 upgrade guide
* `BpostApiClient`, one entry point handing out a client per bpost service
* Geolocator: `x-api-key` and `Accept-Encoding: gzip` headers, both required by manual B.4.1.0
* Geolocator: the mandatory `DD`, `CheckDate` and `CheckOpen` parameters on a nearest-points
  search, and the optional `Info`, `CheckList`, `IncludeBoxNumber`, `IncludeAttributes` and
  `AttributeFilter`
* Geolocator: `PointType::ParcelPoint` (type 16), which was missing entirely
* Geolocator: `ServicePoint` now reads `Country`, `BoxNumber` and the locker `Attributes`
* Shipping Manager: the `bpack XL` product, its mandatory `Dimensions`, and the `fragile` option
* Shipping Manager: ZPL label output, alongside PDF and PNG
* The Announcement API (`POST .../trackedmail/announcement`), which the library never implemented.
  This is the route for anyone printing their own labels: the barcode already exists, and the
  announcement supplies what would otherwise have come with an order
* The Tracking API (`GET .../trackedmail/item/{barcode}/trackingInfo`), likewise never implemented
* Shipping Manager: `RETURNED` as a customs shipment type
* Shipping Manager: the field lengths the manual documents are checked when set, rather than by
  bpost on send — sender and receiver name and company, remark, order reference, cost centre, and
  the 30 kg ceiling that `isValidWeight()` defined but no box ever called. bpack XL and bpack
  Pallet are exempt: they are the products that carry more, and the manual's own bpack XL example
  sends 100 kg

#### Changed

* Root namespace is now `Webatvantage\Bpost\Api\`; the library is split into one namespace per
  bpost service (`Shm`, `Geo`, `Parcel`)
* Minimum PHP version is now 8.5
* Code style is now `webatvantage/php-cs-fixer-config`; static analysis runs PHPStan level 5 and
  the suite runs on PHPUnit 13 — level 8 since, see Unreleased
* Geolocator host is now `pudo.bpost.cloud`, as documented, instead of `pudo.bpost.be`
* A nearest-points search returns a flat list of `ServicePoint`; the distance is a property on the
  point rather than a parallel array key
* Setters drop their `set` prefix and return `$this`; values are read as properties rather than
  through getters
* `getPossibleXValues()` arrays are replaced by enums throughout
* `ext-curl` is a suggestion rather than a requirement: the library no longer calls cURL itself and
  Guzzle works on the stream handler without it
* XML goes through `Support\XmlDocument` and `Support\XmlElement` on both sides, in place of
  `DOMDocument` for writing and `SimpleXMLElement` for reading. `XmlSerializable::toXml()` writes
  into the element it is given rather than returning a loose one, `XmlDeserializable::fromXml()`
  takes an `XmlElement`, and `HttpApiAdapter::request()` returns `XmlElement|string`
* Namespaces are enum cases — `Shm\Enums\ShmNamespace`, `Parcel\Enums\ParcelNamespace` — carrying
  the URI and the prefix together, in place of the prefix strings and namespace constants on the old
  `Xml` helpers. What goes on the wire is unchanged: a generated order is byte-identical, down to
  the order its namespace declarations are written in

#### Removed

* `ext-SimpleXML` is no longer required
* `Support\Xml`, `Shm\Support\Xml` and `Parcel\Support\Xml`, replaced by `Support\XmlDocument`,
  `Support\XmlElement` and the two namespace enums
* `Geo6`, `Geo6Partner`, `Geo6Account`, `Poi`, `Geo6\Day` and `Geo6\Service`, replaced by the
  `Geo` namespace
* `Geo6::getServicePointPage()`, a deprecated alias, and `Geo6::getPointType()`, replaced by the
  `PointType` enum
* `BpostTaxipostLocatorException` and `BpostInvalidDayException`
* `FormHandler`, which built parameters for the Shipping Manager JavaScript widget and made no HTTP
  call; see `MIGRATION.md` for the four-line checksum that replaces it
* `Bpack247` and its customer classes, an undocumented service on a dead host reached over plain
  HTTP
* `Option\Insurance` and `Option\Signature`, aliases deprecated by the upstream project
* `Box\AtIntlHome`, which inherited a parser that reads a different element and so never worked
* `ProductConfiguration\Visibility`, unreferenced and contradicting the values on `DeliveryMethod`
* `BpostOnAppointment`, which appears nowhere in the v3.3.35 manual
* The eight insurance bands above 5 000 EUR, which the library's own validation had always rejected

#### Fixed

* API error responses now reach the caller. `ApiCaller` read the response content type from the
  wrong `curl_getinfo()` key, so every failure surfaced with an empty message
* The service-point page URL carries `Function`, `Partner` and `AppId` again. A refactor in 1.x
  dropped them, producing a URL bpost could not answer
* `AppId` is sent when configured; 1.x stored it and never put it in a request
* `ServicePoint` reads a `<Note>` as well as a `<NOTE>`; a nearest-points search uses the first
  spelling and 1.x only handled the second
* TLS certificate verification is no longer disabled on Geolocator requests
* Label requests send `Content-Type: application/vnd.bpost.shm-labelRequest-v5+XML`. 1.x sent v3,
  which both the manual and every v5 example contradict
* `<unregistered>` writes `<reducedMobilityZone/>`, the element bpost recognises, instead of
  `<parcelLockerReducedMobilityZone/>`, and is read back from a retrieved order
* An international pick-up box can be built at all: `AtIntlPugo::toXML()` called two methods that
  existed nowhere in the library, so it was an unconditional fatal error
* `AtIntlPugo` reads back `receiverName` and `receiverCompany`
* Cash on delivery parses on an international box; the dispatch existed twice and the international
  copy lacked the case
* A box no longer emits an `additionalCustomerReference` nobody set, previously carrying a
  generated `+PHP8.2` suffix
* `Price::forWeight()` reports an overweight parcel in grams, the unit it was given; 1.x compared
  grams and then reported them as kilograms

### 1.1.1 - 2026-09-08

* Escape text values in the generated XML
* Raise the maximum length of an address box from 8 to 9

### 1.1.0 - 2026-07-09

* Split the Geolocator into a partner and an account client
* Add the collection of all service points
* Throw when the credentials a call needs are not set
* Uppercase the language codes
* Remove the version field from composer.json

### 1.0.0 - 2026-02-24

First release under `webatvantage/bpost-api-library`, forked from
`antidot-be/bpost-api-library`.

---

The entries below are the upstream project's history, kept for reference. They were never released
under this package name, which is why the numbering restarts at 1.0.0 above.

### 3.7.0

* Add info in the additional customer reference for bpost statistics

### 3.6.0

* Add compatibility with AtIntlPugo and AtIntlHome
* Update insurance amounts
* Fix options
* Update restrictions of the form handler
* Store XML namespaces as PHP constants

### 3.5.1

* fix: API returns xml-namespaces v3 instead of v5
* fix: Some options were not prefixed (xml-namespace)
* feat: bpost API URL is now : https://shm-rest.bpost.cloud/services/shm
* tests: update E2E tests (tests which called the bpost API)

### 3.5.0

* Externalize creation of HTTP requests in new classes (HttpRequestBuilder\*)
* Minimize duplication of code
* Move tests in a specific (PHP) namespace and apply PHP-CS rules
* Rename class Insurance->Insured to avoid exception during xml parsing
* Update HTTP headers and XML namespace for api v5 #32
* Add CustomsInfo#currency and CustomsInfo#amtPostagePaidByAddresse
* Rename class Signature->Signed to avoid exception during xml parsing
* Add parcelContents for International

### 3.4.11

* Fix previously broken unit tests
* Add github-actions
* Format code by following PSR-12

### 3.4.10

* throw BpostInvalidXmlResponseException if XML response if not a valid XML

### 3.4.9

* Geo6.php supports Country
* Fix string/int comparison in ApiCaller
Add PHP7.1 -> 8.0 to travis CI job

### 3.4.8

* Update version of some composer packages

### 3.4.7

* Endpoint change for Geo6/Pudo

### 3.4.6

* For National, option Insured must call class Insurance
* Fixed the parcel locker 'unregistered', UnregisteredParceLockerMember is deprecated #16

### 3.4.5

* Fix PHP signatures

### 3.4.1

* Refactoring
* Fix issues

### 3.4.0

* Add retro-compatibility with tijsverkoyen library (namespace changes)
* Complete the README (examples, broken links, ...)
* Change API URL (api.bpost.be -> api-parcel.bpost.be)
* Labels features
  * Possibility to append field "order reference"
  * Possibility to force printing
* Geo6 features
  * Geo6 is now called via HTTPS
  * Send data to API via POST
  * Add Geo6::getPointType() to calculate point types
* Products features
  * Add "bpack World Easy Return" to international products
  * Box At247 can contain a product bpack 24/7

### 3.3.0

* Use bpost API version 3.3 (yet, bpack part only)
* Change namespace TijsVerkoyen\Bpost to Bpost\BpostApiClient
* Add more unit tests to perform code coverage
* Begin to based the unit tests on XML examples [given by bpost](http://bpost.freshdesk.com/support/solutions/articles/4000037653-where-can-i-find-the-bpack-integration-manual-examples-and-xsd-s-)
* Add CONTRIBUTING.md

### 3.0.1

* Allowed SaturdayDelivery, see https://github.com/tijsverkoyen/bpost/pull/11

### 3.0.0

* Bugfix: removed usage of undefined constant, see https://github.com/tijsverkoyen/bpost/pull/8


### 1.0.1

* Made the classes compliant with PSR
* Using Namespaces
* From now on we will follow the versionnumbers that bpost is using, so we will
  skip a major version
* Introduction of the GEO-services
* Introduction of the Bpack24/7-services
* Composer support
* Decent objects
