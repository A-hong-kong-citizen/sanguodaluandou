<?php
namespace Sanguoga\Cards;

require_once __DIR__ . '/../abstract.php';

abstract class OrdinaryCardEffect extends CardEffect
{
	public function category(): string
	{
		return '普通';
	}
}