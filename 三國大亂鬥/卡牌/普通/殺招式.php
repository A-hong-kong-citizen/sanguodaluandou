<?php
namespace Sanguoga\Cards;

require_once __DIR__ . '/../abstract.php';
require_once __DIR__ . '/../PlayingCard.php';

final class ShaCard extends CardEffect implements PlayingCard
{
	use HasCardAttributes;

	private string $effectName;
	private int $requiredDodges;

	public function __construct(array $attributes, string $effectName = '殺', int $requiredDodges = 1)
	{
		if ($requiredDodges < 1) {
			throw new \InvalidArgumentException('A Sha attack must require at least one dodge.');
		}

		$this->initializeCardAttributes($attributes);
		$this->effectName = $effectName;
		$this->requiredDodges = $requiredDodges;
	}

	public function name(): string
	{
		return $this->effectName;
	}

	public function category(): string
	{
		return '普通';
	}

	public function isAttack(): bool
	{
		return true;
	}

	public function damage(): int
	{
		return match ($this->effectName) {
			'火殺' => 2,
			'雷殺' => 3,
			default => 1,
		};
	}

	public function requiredDodges(): int
	{
		return $this->requiredDodges;
	}
}
