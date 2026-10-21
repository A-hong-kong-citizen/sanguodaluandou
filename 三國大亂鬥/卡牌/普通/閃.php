<?php
namespace Sanguoga\Cards;

require_once __DIR__ . '/abstract.php';

final class ShanCard extends CardEffect
{
	public function name(): string
	{
		return '閃';
	}

	public function category(): string
	{
		return '普通';
	}
}
