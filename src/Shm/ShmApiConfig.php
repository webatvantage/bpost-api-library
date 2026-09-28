<?php

namespace Webatvantage\Bpost\Api\Shm;

readonly class ShmApiConfig
{
	/**
	 * @param string $accountId Your bpost account id, which is also the HTTP Basic username and
	 *                          appears in every Shipping Manager URL
	 * @param string $passphrase The Shipping Manager webservice password, from the Admin panel of
	 *                           the Shipping Manager backend. Not your bpost portal login
	 * @param string $baseUri
	 */
	public function __construct(
		public string $accountId,
		public string $passphrase,
		public string $baseUri = 'https://shm-rest.bpost.cloud/services/shm',
	) {}

	public function authorizationHeader(): string
	{
		return 'Basic ' . base64_encode($this->accountId . ':' . $this->passphrase);
	}
}
