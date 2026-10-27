<?php
namespace Sanguoga\Cards;

require_once __DIR__ . '/../abstract.php';
require_once __DIR__ . '/../PlayingCard.php';

abstract class TrickCardEffect extends CardEffect implements PlayingCard
{
	use HasCardAttributes;

	public function __construct(array $attributes)
	{
		$this->initializeCardAttributes($attributes);
	}

	public function category(): string
	{
		return '錦囊';
	}

	public function needsTarget(): bool
	{
		return true;
	}

	abstract public function apply(array &$game, string $user, ?int $targetIndex = null, ?string $targetSide = null): array;
}
