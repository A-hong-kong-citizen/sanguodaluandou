<?php
namespace Sanguoga\Cards;

require_once __DIR__ . '/../abstract.php';

abstract class TrickCardEffect extends CardEffect
{
	public function category(): string
	{
		return '錦囊';
	}

	public function needsTarget(): bool
	{
		return true;
	}

	abstract public function apply(array &$game, array $card, string $user, ?int $targetIndex = null): array;
}
