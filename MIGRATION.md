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
| _(Phase 1 — Geo)_ | |
| _(Phase 2 — Shipping Manager)_ | |
| _(Phase 3 — Parcel)_ | |

## 3. Removed with no replacement

Filled in per phase.

| Removed | Why | What to do |
|---|---|---|
