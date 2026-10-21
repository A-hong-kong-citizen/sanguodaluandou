<?php
namespace Sanguoga\Cards;

require_once __DIR__ . '/abstract.php';

final class GuoheChaiqiaoCard extends TrickCardEffect
{
	public function name(): string
	{
		return '過河拆橋';
	}

	public function apply(array &$game, array $card, string $user, ?int $targetIndex = null): array
	{
		$targetSide = $user === 'player' ? 'bot' : 'player';
		if ($targetIndex === null || !isset($game['hands'][$targetSide][$targetIndex])) {
			$game['discard'][] = $card;
			if ($user === 'bot') {
				return [
					'log' => 'Bot 使用過河拆橋，但你沒有手牌可選。',
					'bot_message' => 'Bot 使用過河拆橋，但你沒有手牌可供選擇。',
				];
			}

			return ['log' => '你使用過河拆橋，但 Bot 沒有手牌可選。'];
		}

		$target = array_splice($game['hands'][$targetSide], $targetIndex, 1)[0];
		$game['discard'][] = $card;
		$game['discard'][] = $target;

		if ($user === 'bot') {
			return [
				'log' => 'Bot 使用過河拆橋並棄置你的一張牌。',
				'bot_message' => 'Bot 對你使用過河拆橋，你失去一張手牌，該牌已放入棄置牌堆。',
			];
		}

		return ['log' => '你使用過河拆橋，棄置 Bot 的一張牌。'];
	}
}