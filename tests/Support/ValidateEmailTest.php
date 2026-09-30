<?php

namespace Webatvantage\Bpost\Api\Tests\Support;

use PHPUnit\Framework\Attributes\DataProvider;
use Webatvantage\Bpost\Api\Exceptions\InvalidLengthException;
use Webatvantage\Bpost\Api\Exceptions\InvalidPatternException;
use Webatvantage\Bpost\Api\Parcel\DataObjects\ContactDetail;
use Webatvantage\Bpost\Api\Parcel\DataObjects\Options\Notification;
use Webatvantage\Bpost\Api\Shm\DataObjects\Box\Unregistered;
use Webatvantage\Bpost\Api\Shm\DataObjects\Options\Messaging;
use Webatvantage\Bpost\Api\Shm\DataObjects\Receiver;
use Webatvantage\Bpost\Api\Tests\TestCase;

class ValidateEmailTest extends TestCase
{
	/**
	 * Every setter that takes an address, as a callable taking the address.
	 *
	 * @return array<string, array{callable(string): object}>
	 */
	public static function setters(): array
	{
		return [
			'Shm Customer' => [static fn (string $email) => new Receiver()->emailAddress($email)],
			'Shm Messaging' => [static fn (string $email) => Messaging::infoDistributed()->email($email)],
			'Shm Unregistered' => [static fn (string $email) => new Unregistered()->emailAddress($email)],
			'Parcel ContactDetail' => [static fn (string $email) => new ContactDetail()->emailAddress($email)],
			'Parcel Notification' => [static fn (string $email) => Notification::infoDistributed()->email($email)],
		];
	}

	/**
	 * @param callable(string): object $set
	 */
	#[DataProvider('setters')]
	public function test_it_takes_an_address_bpost_can_deliver_to(callable $set)
	{
		$this->assertSame('alma@example.com', $set('alma@example.com')->emailAddress);
	}

	/**
	 * bpost answers a malformed address by accepting the order and never sending the message, so
	 * nothing downstream reports the typo.
	 *
	 * @param callable(string): object $set
	 */
	#[DataProvider('setters')]
	public function test_it_refuses_an_address_bpost_cannot_deliver_to(callable $set)
	{
		$this->expectException(InvalidPatternException::class);

		$set('alma.example.com');
	}

	/**
	 * @return array<string, array{string}>
	 */
	public static function unusableAddresses(): array
	{
		return [
			'no at sign' => ['alma.example.com'],
			'no domain' => ['alma@'],
			'no local part' => ['@example.com'],
			'a space inside' => ['alma smith@example.com'],
			'empty' => [''],
			'two at signs' => ['alma@@example.com'],
		];
	}

	#[DataProvider('unusableAddresses')]
	public function test_it_names_the_field_and_the_value_it_refused(string $email)
	{
		try
		{
			new Receiver()->emailAddress($email);
		}
		catch (InvalidPatternException $exception)
		{
			$this->assertSame('emailAddress', $exception->name);
			$this->assertSame($email, $exception->value);

			return;
		}

		$this->fail(sprintf('"%s" should not be accepted as an address.', $email));
	}

	/**
	 * The documented length is still bpost's rule, and it is reported as a length problem rather
	 * than as a malformed address.
	 */
	public function test_the_documented_length_is_still_checked()
	{
		$this->expectException(InvalidLengthException::class);

		new ContactDetail()->emailAddress(str_repeat('a', 32) . '@example.com');
	}

	/**
	 * Reading is not judged by the sending rules, and fromXml() assigns without the setters, so an
	 * address bpost already holds comes back whatever shape it is in.
	 */
	public function test_an_address_in_a_response_is_reported_as_bpost_holds_it()
	{
		$detail = ContactDetail::fromXml($this->parse(
			'<contactDetail><emailAddress>not an address at all</emailAddress></contactDetail>',
		));

		$this->assertSame('not an address at all', $detail->emailAddress);
	}
}
