<?php
namespace Sanguoga\Cards;

require_once __DIR__ . '/../abstract.php';
require_once __DIR__ . '/../PlayingCard.php';

final class ShanCard extends CardEffect implements PlayingCard
{
	use HasCardAttributes;

	public function __construct(array $attributes)
	{
		$this->initializeCardAttributes($attributes);
	}

	public function name(): string
	{
		return '閃';
	}

	public function category(): string
	{
		return '普通';
	}
}
