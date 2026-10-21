<?php
namespace Sanguoga\Cards;

abstract class CardEffect
{
	abstract public function name(): string;
	abstract public function category(): string;

	public function isAttack(): bool
	{
		return false;
	}

	public function damage(): int
	{
		return 0;
	}
}