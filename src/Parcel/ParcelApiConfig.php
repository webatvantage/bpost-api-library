<?php

namespace Webatvantage\Bpost\Api\Parcel;

readonly class ParcelApiConfig
{
	/**
	 * @param string $accountId Your bpost account id, which is also the HTTP Basic username
	 * @param string $password Issued separately from the Shipping Manager passphrase; request it
	 *                         from esolutions@bpost.be
	 * @param string $baseUri
	 */
	public function __construct(
		public string $accountId,
		public string $password,
		public string $baseUri = 'https://api.parcel.bpost.cloud',
	) {}

	public function authorizationHeader(): string
	{
		return 'Basic ' . base64_encode($this->accountId . ':' . $this->password);
	}
}
