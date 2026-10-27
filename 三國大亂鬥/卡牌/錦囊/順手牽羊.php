<?php
namespace Sanguoga\Cards;

require_once __DIR__ . '/abstract.php';

final class ShunshouQianyangCard extends TrickCardEffect
{
	public function name(): string
	{
		return '順手牽羊';
	}

	public function apply(array &$game, string $user, ?int $targetIndex = null, ?string $targetSide = null): array
	{
		$targetSide ??= $user === 'player' ? 'bot' : 'player';
		if ($targetIndex === null || !isset($game['hands'][$targetSide][$targetIndex])) {
			$game['discard'][] = $this;
			if ($user === 'bot') {
				return [
					'log' => 'Bot 使用順手牽羊，但你沒有手牌可選。',
					'bot_message' => 'Bot 使用順手牽羊，但你沒有手牌可供選擇。',
				];
			}

			return ['log' => '你使用順手牽羊，但 Bot 沒有手牌可選。'];
		}

		$target = array_splice($game['hands'][$targetSide], $targetIndex, 1)[0];
		$game['hands'][$user][] = $target;
		$game['discard'][] = $this;

		if ($user === 'bot') {
			return [
				'log' => 'Bot 使用順手牽羊並取得你的一張牌。',
				'bot_message' => 'Bot 對你使用順手牽羊，你失去一張手牌，Bot 將這張牌收入其手牌。',
			];
		}

		return ['log' => '你使用順手牽羊，取得 Bot 的一張牌。'];
	}
}