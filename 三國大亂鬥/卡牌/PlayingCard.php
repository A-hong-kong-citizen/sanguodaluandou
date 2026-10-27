<?php
namespace Sanguoga\Cards;

interface PlayingCard extends \ArrayAccess
{
	public function name(): string;
	public function category(): string;
	public function isAttack(): bool;
	public function damage(): int;
}

trait HasCardAttributes
{
	private array $attributes;

	protected function initializeCardAttributes(array $attributes): void
	{
		$this->attributes = $attributes;
	}

	public function offsetExists(mixed $offset): bool
	{
		return isset($this->attributes[$offset]);
	}

	public function offsetGet(mixed $offset): mixed
	{
		return $this->attributes[$offset];
	}

	public function offsetSet(mixed $offset, mixed $value): void
	{
		throw new \LogicException('Playing cards are immutable.');
	}

	public function offsetUnset(mixed $offset): void
	{
		throw new \LogicException('Playing cards are immutable.');
	}
}