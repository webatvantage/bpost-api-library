<?php

namespace Webatvantage\Bpost\Api\Geo\Traits;

use Webatvantage\Bpost\Api\Contracts\Request;
use Webatvantage\Bpost\Api\Geo\Enums\LockerType;

/**
 * The AttributeFilter parameter, which a search and an all-points download both take.
 *
 * Only lockers in Belgium carry the attributes it filters on, and both filters can be applied at
 * once, which is why it accumulates rather than overwriting — bpost expects the key repeated.
 *
 * @phpstan-require-extends Request
 */
trait HasAttributeFilter
{
	/** @var array<string> */
	private array $attributeFilters = [];

	public function filterLockerType(LockerType $lockerType): static
	{
		return $this->addAttributeFilter('LOCKERTYPE:' . $lockerType->filterValue());
	}

	public function filterNightDelivery(bool $allowed = true): static
	{
		return $this->addAttributeFilter('NIGHTDELIVERY:' . ($allowed ? 'TRUE' : 'FALSE'));
	}

	private function addAttributeFilter(string $filter): static
	{
		$this->attributeFilters[] = $filter;

		return $this->addParameter('AttributeFilter', $this->attributeFilters);
	}
}
