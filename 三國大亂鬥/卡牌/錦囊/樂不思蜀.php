<?php
namespace Sanguoga\Cards;

require_once __DIR__ . '/abstract.php';

final class LebusishuCard extends TrickCardEffect
{
	public function name(): string
	{
		return '樂不思蜀';
	}

	public function needsTarget(): bool
	{
		return false;
	}

	public function apply(array &$game, string $user, ?int $targetIndex = null, ?string $targetSide = null): array
	{
		$targetSide ??= $user === 'player' ? 'bot' : 'player';
		$game['delayed'][$targetSide][] = $this;

		if ($user === 'bot') {
			return [
				'log' => 'Bot 對你使用樂不思蜀。',
				'bot_message' => 'Bot 對你使用樂不思蜀，這張牌已放到你身上，並會在你下回合判定。',
			];
		}

		return ['log' => '你對 Bot 使用樂不思蜀。'];
	}
}