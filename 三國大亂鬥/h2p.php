<?php
use Sanguoga\Cards\GuoheChaiqiaoCard;
use Sanguoga\Cards\LebusishuCard;
use Sanguoga\Cards\PlayingCard;
use Sanguoga\Cards\ShaCard;
use Sanguoga\Cards\ShanCard;
use Sanguoga\Cards\ShunshouQianyangCard;
use Sanguoga\Cards\TaoCard;
use Sanguoga\Cards\TrickCardEffect;

require_once __DIR__ . '/卡牌/普通/殺招式.php';
require_once __DIR__ . '/卡牌/普通/閃.php';
require_once __DIR__ . '/卡牌/普通/桃.php';
require_once __DIR__ . '/卡牌/PlayingCard.php';
require_once __DIR__ . '/卡牌/錦囊/順手牽羊.php';
require_once __DIR__ . '/卡牌/錦囊/過河拆橋.php';
require_once __DIR__ . '/卡牌/錦囊/樂不思蜀.php';

session_start();

function gameEscape(string $value): string
{
	return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function addGameLog(array &$game, string $message): void
{
	array_unshift($game['log'], $message);
	$game['log'] = array_slice($game['log'], 0, 10);
}

function createGame(): array
{
	$attributes = static fn (string $id, string $name, string $symbol, string $color, string $label, int $value): array => [
			'id' => $id,
			'name' => $name,
			'symbol' => $symbol,
			'color' => $color,
			'label' => $label,
			'value' => $value,
		];
	$cards = [
			new LebusishuCard($attributes('spades-A-1', '黑桃', '♠', 'black', 'A', 14)),
			new LebusishuCard($attributes('spades-A-2', '黑桃', '♠', 'black', 'A', 14)),
			new ShanCard($attributes('spades-2-1', '黑桃', '♠', 'black', '2', 2)),
			new ShanCard($attributes('spades-2-2', '黑桃', '♠', 'black', '2', 2)),
			new ShaCard($attributes('spades-3-1', '黑桃', '♠', 'black', '3', 3), '火殺'),
			new ShaCard($attributes('spades-3-2', '黑桃', '♠', 'black', '3', 3), '火殺'),
			new ShaCard($attributes('spades-4-1', '黑桃', '♠', 'black', '4', 4), '雷殺'),
			new ShaCard($attributes('spades-4-2', '黑桃', '♠', 'black', '4', 4), '雷殺'),
			new ShanCard($attributes('spades-5-1', '黑桃', '♠', 'black', '5', 5)),
			new ShanCard($attributes('spades-5-2', '黑桃', '♠', 'black', '5', 5)),
			new ShaCard($attributes('spades-6-1', '黑桃', '♠', 'black', '6', 6), '火殺'),
			new ShaCard($attributes('spades-6-2', '黑桃', '♠', 'black', '6', 6), '火殺'),
			new ShunshouQianyangCard($attributes('spades-7-1', '黑桃', '♠', 'black', '7', 7)),
			new ShunshouQianyangCard($attributes('spades-7-2', '黑桃', '♠', 'black', '7', 7)),
			new ShaCard($attributes('spades-8-1', '黑桃', '♠', 'black', '8', 8), '雷殺'),
			new ShaCard($attributes('spades-8-2', '黑桃', '♠', 'black', '8', 8), '雷殺'),
			new ShaCard($attributes('spades-9-1', '黑桃', '♠', 'black', '9', 9), '火殺'),
			new ShaCard($attributes('spades-9-2', '黑桃', '♠', 'black', '9', 9), '火殺'),
			new ShanCard($attributes('spades-10-1', '黑桃', '♠', 'black', '10', 10)),
			new ShanCard($attributes('spades-10-2', '黑桃', '♠', 'black', '10', 10)),
			new ShanCard($attributes('spades-J-1', '黑桃', '♠', 'black', 'J', 11)),
			new ShanCard($attributes('spades-J-2', '黑桃', '♠', 'black', 'J', 11)),
			new ShaCard($attributes('spades-Q-1', '黑桃', '♠', 'black', 'Q', 12), '雷殺'),
			new ShaCard($attributes('spades-Q-2', '黑桃', '♠', 'black', 'Q', 12), '雷殺'),
			new ShanCard($attributes('spades-K-1', '黑桃', '♠', 'black', 'K', 13)),
			new ShanCard($attributes('spades-K-2', '黑桃', '♠', 'black', 'K', 13)),
			new LebusishuCard($attributes('clubs-A-1', '梅花', '♣', 'black', 'A', 14)),
			new LebusishuCard($attributes('clubs-A-2', '梅花', '♣', 'black', 'A', 14)),
			new ShanCard($attributes('clubs-2-1', '梅花', '♣', 'black', '2', 2)),
			new ShanCard($attributes('clubs-2-2', '梅花', '♣', 'black', '2', 2)),
			new ShaCard($attributes('clubs-3-1', '梅花', '♣', 'black', '3', 3), '火殺'),
			new ShaCard($attributes('clubs-3-2', '梅花', '♣', 'black', '3', 3), '火殺'),
			new ShaCard($attributes('clubs-4-1', '梅花', '♣', 'black', '4', 4), '雷殺'),
			new ShaCard($attributes('clubs-4-2', '梅花', '♣', 'black', '4', 4), '雷殺'),
			new ShanCard($attributes('clubs-5-1', '梅花', '♣', 'black', '5', 5)),
			new ShanCard($attributes('clubs-5-2', '梅花', '♣', 'black', '5', 5)),
			new ShaCard($attributes('clubs-6-1', '梅花', '♣', 'black', '6', 6), '火殺'),
			new ShaCard($attributes('clubs-6-2', '梅花', '♣', 'black', '6', 6), '火殺'),
			new ShunshouQianyangCard($attributes('clubs-7-1', '梅花', '♣', 'black', '7', 7)),
			new ShunshouQianyangCard($attributes('clubs-7-2', '梅花', '♣', 'black', '7', 7)),
			new ShaCard($attributes('clubs-8-1', '梅花', '♣', 'black', '8', 8), '雷殺'),
			new ShaCard($attributes('clubs-8-2', '梅花', '♣', 'black', '8', 8), '雷殺'),
			new ShaCard($attributes('clubs-9-1', '梅花', '♣', 'black', '9', 9), '火殺'),
			new ShaCard($attributes('clubs-9-2', '梅花', '♣', 'black', '9', 9), '火殺'),
			new ShanCard($attributes('clubs-10-1', '梅花', '♣', 'black', '10', 10)),
			new ShanCard($attributes('clubs-10-2', '梅花', '♣', 'black', '10', 10)),
			new ShanCard($attributes('clubs-J-1', '梅花', '♣', 'black', 'J', 11)),
			new ShanCard($attributes('clubs-J-2', '梅花', '♣', 'black', 'J', 11)),
			new ShaCard($attributes('clubs-Q-1', '梅花', '♣', 'black', 'Q', 12), '雷殺'),
			new ShaCard($attributes('clubs-Q-2', '梅花', '♣', 'black', 'Q', 12), '雷殺'),
			new ShanCard($attributes('clubs-K-1', '梅花', '♣', 'black', 'K', 13)),
			new ShanCard($attributes('clubs-K-2', '梅花', '♣', 'black', 'K', 13)),
			new LebusishuCard($attributes('hearts-A-1', '紅心', '♥', 'red', 'A', 14)),
			new LebusishuCard($attributes('hearts-A-2', '紅心', '♥', 'red', 'A', 14)),
			new TaoCard($attributes('hearts-2-1', '紅心', '♥', 'red', '2', 2)),
			new TaoCard($attributes('hearts-2-2', '紅心', '♥', 'red', '2', 2)),
			new ShaCard($attributes('hearts-3-1', '紅心', '♥', 'red', '3', 3), '火殺'),
			new ShaCard($attributes('hearts-3-2', '紅心', '♥', 'red', '3', 3), '火殺'),
			new ShaCard($attributes('hearts-4-1', '紅心', '♥', 'red', '4', 4), '雷殺'),
			new ShaCard($attributes('hearts-4-2', '紅心', '♥', 'red', '4', 4), '雷殺'),
			new ShaCard($attributes('hearts-5-1', '紅心', '♥', 'red', '5', 5)),
			new ShaCard($attributes('hearts-5-2', '紅心', '♥', 'red', '5', 5)),
			new ShaCard($attributes('hearts-6-1', '紅心', '♥', 'red', '6', 6), '火殺'),
			new ShaCard($attributes('hearts-6-2', '紅心', '♥', 'red', '6', 6), '火殺'),
			new GuoheChaiqiaoCard($attributes('hearts-7-1', '紅心', '♥', 'red', '7', 7)),
			new GuoheChaiqiaoCard($attributes('hearts-7-2', '紅心', '♥', 'red', '7', 7)),
			new ShaCard($attributes('hearts-8-1', '紅心', '♥', 'red', '8', 8), '雷殺'),
			new ShaCard($attributes('hearts-8-2', '紅心', '♥', 'red', '8', 8), '雷殺'),
			new ShaCard($attributes('hearts-9-1', '紅心', '♥', 'red', '9', 9), '火殺'),
			new ShaCard($attributes('hearts-9-2', '紅心', '♥', 'red', '9', 9), '火殺'),
			new ShaCard($attributes('hearts-10-1', '紅心', '♥', 'red', '10', 10)),
			new ShaCard($attributes('hearts-10-2', '紅心', '♥', 'red', '10', 10)),
			new ShaCard($attributes('hearts-J-1', '紅心', '♥', 'red', 'J', 11)),
			new ShaCard($attributes('hearts-J-2', '紅心', '♥', 'red', 'J', 11)),
			new ShaCard($attributes('hearts-Q-1', '紅心', '♥', 'red', 'Q', 12), '雷殺'),
			new ShaCard($attributes('hearts-Q-2', '紅心', '♥', 'red', 'Q', 12), '雷殺'),
			new ShaCard($attributes('hearts-K-1', '紅心', '♥', 'red', 'K', 13)),
			new ShaCard($attributes('hearts-K-2', '紅心', '♥', 'red', 'K', 13)),
			new LebusishuCard($attributes('diamonds-A-1', '方塊', '♦', 'red', 'A', 14)),
			new LebusishuCard($attributes('diamonds-A-2', '方塊', '♦', 'red', 'A', 14)),
			new ShaCard($attributes('diamonds-2-1', '方塊', '♦', 'red', '2', 2)),
			new ShaCard($attributes('diamonds-2-2', '方塊', '♦', 'red', '2', 2)),
			new ShaCard($attributes('diamonds-3-1', '方塊', '♦', 'red', '3', 3), '火殺'),
			new ShaCard($attributes('diamonds-3-2', '方塊', '♦', 'red', '3', 3), '火殺'),
			new ShaCard($attributes('diamonds-4-1', '方塊', '♦', 'red', '4', 4), '雷殺'),
			new ShaCard($attributes('diamonds-4-2', '方塊', '♦', 'red', '4', 4), '雷殺'),
			new ShaCard($attributes('diamonds-5-1', '方塊', '♦', 'red', '5', 5)),
			new ShaCard($attributes('diamonds-5-2', '方塊', '♦', 'red', '5', 5)),
			new ShaCard($attributes('diamonds-6-1', '方塊', '♦', 'red', '6', 6), '火殺'),
			new ShaCard($attributes('diamonds-6-2', '方塊', '♦', 'red', '6', 6), '火殺'),
			new GuoheChaiqiaoCard($attributes('diamonds-7-1', '方塊', '♦', 'red', '7', 7)),
			new GuoheChaiqiaoCard($attributes('diamonds-7-2', '方塊', '♦', 'red', '7', 7)),
			new ShaCard($attributes('diamonds-8-1', '方塊', '♦', 'red', '8', 8), '雷殺'),
			new ShaCard($attributes('diamonds-8-2', '方塊', '♦', 'red', '8', 8), '雷殺'),
			new ShaCard($attributes('diamonds-9-1', '方塊', '♦', 'red', '9', 9), '火殺'),
			new ShaCard($attributes('diamonds-9-2', '方塊', '♦', 'red', '9', 9), '火殺'),
			new ShaCard($attributes('diamonds-10-1', '方塊', '♦', 'red', '10', 10)),
			new ShaCard($attributes('diamonds-10-2', '方塊', '♦', 'red', '10', 10)),
			new ShaCard($attributes('diamonds-J-1', '方塊', '♦', 'red', 'J', 11)),
			new ShaCard($attributes('diamonds-J-2', '方塊', '♦', 'red', 'J', 11)),
			new ShaCard($attributes('diamonds-Q-1', '方塊', '♦', 'red', 'Q', 12), '雷殺'),
			new ShaCard($attributes('diamonds-Q-2', '方塊', '♦', 'red', 'Q', 12), '雷殺'),
			new ShaCard($attributes('diamonds-K-1', '方塊', '♦', 'red', 'K', 13)),
			new ShaCard($attributes('diamonds-K-2', '方塊', '♦', 'red', 'K', 13)),
	];
	shuffle($cards);
	$deck = new \SplQueue();
	foreach ($cards as $card) {
		$deck->enqueue($card);
	}

	$hands = ['player' => [], 'bot' => []];
	foreach (['player', 'bot'] as $side) {
		for ($drawn = 0; $drawn < 4; $drawn++) {
			$hands[$side][] = $deck->dequeue();
		}
	}

	$game = [
		'card_model_version' => 2,
		'hands' => $hands,
		'deck' => $deck,
		'discard' => [],
		'check_deck' => [],
		'delayed' => ['player' => [], 'bot' => []],
		'health' => ['player' => 5, 'bot' => 5],
		'turn' => 'player',
		'stage' => 'take',
		'skip_play' => false,
		'attack_used' => false,
		'pending_target' => null,
		'response' => null,
		'bot_messages' => [],
		'winner' => null,
		'log' => [],
	];
	addGameLog($game, '新對局開始：雙方各獲得 4 張牌，生命值為 5。');
	startGameTurn($game, 'player');

	return $game;
}

function startGameTurn(array &$game, string $side): void
{
	$game['turn'] = $side;
	$game['stage'] = 'take';
	$game['skip_play'] = false;
	$game['attack_used'] = false;
	$sideName = $side === 'player' ? '你' : 'Bot';
	$pending = $game['delayed'][$side];
	$game['delayed'][$side] = [];

	if (!$pending) {
		addGameLog($game, $sideName . '的檢查階段：沒有延遲判定。');
		return;
	}

	foreach ($pending as $indulgence) {
		$game['discard'][] = $indulgence;
		if ($game['deck']->isEmpty()) {
			addGameLog($game, $sideName . '的樂不思蜀沒有牌可翻，判定結束。');
			continue;
		}
		$revealed = $game['deck']->dequeue();

		$game['check_deck'][] = $revealed;
		if ($revealed['symbol'] === '♥') {
			addGameLog($game, $sideName . '翻出紅心 ' . $revealed['label'] . '，樂不思蜀無效。');
		} else {
			$game['skip_play'] = true;
			addGameLog($game, $sideName . '翻出非紅心 ' . $revealed['symbol'] . $revealed['label'] . '，抽牌後跳過本回合其餘階段。');
		}
	}
}

function drawGameCards(array &$game, string $side, int $amount): int
{
	$drawn = 0;
	while ($drawn < $amount && !$game['deck']->isEmpty()) {
		$game['hands'][$side][] = $game['deck']->dequeue();
		$drawn++;
	}
	addGameLog($game, ($side === 'player' ? '你' : 'Bot') . '從牌堆抽取 ' . $drawn . ' 張牌。');

	return $drawn;
}

function findGameCardIndex(array $hand, string $effectClass): ?int
{
	foreach ($hand as $index => $card) {
		if ($card instanceof $effectClass) {
			return $index;
		}
	}

	return null;
}

function removeGameCard(array &$game, string $side, string $cardId): ?PlayingCard
{
	foreach ($game['hands'][$side] as $index => $card) {
		if ($card['id'] === $cardId) {
			return array_splice($game['hands'][$side], $index, 1)[0];
		}
	}

	return null;
}

function damageGameSide(array &$game, string $side, int $damage): void
{
	$game['health'][$side] = max(0, $game['health'][$side] - $damage);
	$sideName = $side === 'player' ? '你' : 'Bot';
	addGameLog($game, $sideName . '受到 ' . $damage . ' 點傷害，剩餘生命 ' . $game['health'][$side] . '。');

	if ($game['health'][$side] === 0) {
		$game['winner'] = $side === 'player' ? 'bot' : 'player';
		$game['stage'] = 'game_over';
		addGameLog($game, $game['winner'] === 'player' ? '你贏了！' : '你輸了。');
	}
}

function finishBotTurn(array &$game, bool $skipped = false): void
{
	if (!$skipped) {
		while (count($game['hands']['bot']) > $game['health']['bot']) {
			$index = array_rand($game['hands']['bot']);
			$card = array_splice($game['hands']['bot'], $index, 1)[0];
			$game['discard'][] = $card;
			addGameLog($game, 'Bot 隨機棄置 ' . $card['name'] . $card['label'] . '。');
		}
	}

	if ($game['stage'] !== 'game_over') {
		startGameTurn($game, 'player');
	}
}

function runBotTurn(array &$game): void
{
	$game['bot_messages'] = [];
	startGameTurn($game, 'bot');
	drawGameCards($game, 'bot', 2);
	if ($game['skip_play']) {
		addGameLog($game, 'Bot 跳過出牌與棄牌階段。');
		finishBotTurn($game, true);
		return;
	}
	while ($game['health']['bot'] < 5) {
		$peachIndex = findGameCardIndex($game['hands']['bot'], TaoCard::class);
		if ($peachIndex === null) {
			break;
		}
		$peach = array_splice($game['hands']['bot'], $peachIndex, 1)[0];
		$game['discard'][] = $peach;
		$game['health']['bot']++;
		addGameLog($game, 'Bot 使用桃，回覆 1 點生命。');
	}

	while (true) {
		$specialIndex = null;
		foreach ($game['hands']['bot'] as $index => $card) {
			if ($card instanceof TrickCardEffect) {
				$specialIndex = $index;
				break;
			}
		}
		if ($specialIndex === null) {
			break;
		}

		$card = array_splice($game['hands']['bot'], $specialIndex, 1)[0];
		$effect = $card;
		$targetIndex = $effect->needsTarget() && $game['hands']['player']
			? array_rand($game['hands']['player'])
			: null;
		$result = $effect->apply($game, 'bot', $targetIndex);
		if (isset($result['bot_message'])) {
			$game['bot_messages'][] = $result['bot_message'];
		}
		addGameLog($game, $result['log']);
	}

	$attackIndex = null;
	foreach ($game['hands']['bot'] as $index => $card) {
		if ($card->isAttack()) {
			$attackIndex = $index;
			break;
		}
	}
	if ($attackIndex !== null) {
		$attack = array_splice($game['hands']['bot'], $attackIndex, 1)[0];
		$effect = $attack;
		if (!$effect instanceof ShaCard) {
			throw new LogicException('Attack cards must use ShaCard effects.');
		}
		$game['attack_used'] = true;
		$game['response'] = [
			'card' => $attack,
			'damage' => $effect->damage(),
			'effect' => $effect->name(),
			'required_dodges' => $effect->requiredDodges(),
			'received_dodges' => 0,
		];
		$game['stage'] = 'response';
		addGameLog($game, 'Bot 對你使用' . $effect->name() . '，需要 ' . $effect->requiredDodges() . ' 張閃。');
		return;
	}

	finishBotTurn($game);
}

function finishPlayerTurn(array &$game): void
{
	if ($game['stage'] !== 'game_over') {
		runBotTurn($game);
	}
}

function handleGameAction(array &$game, string $action, array $post): void
{
	if ($action === 'new_game') {
		$game = createGame();
		return;
	}
	if ($game['stage'] === 'game_over') {
		return;
	}

	switch ($action) {
		case 'take':
			if ($game['turn'] !== 'player' || $game['stage'] !== 'take') {
				return;
			}
			drawGameCards($game, 'player', 2);
			if ($game['skip_play']) {
				addGameLog($game, '你跳過出牌與棄牌階段。');
				finishPlayerTurn($game);
			} else {
				$game['stage'] = 'play';
			}
			return;

		case 'play_card':
			if ($game['turn'] !== 'player' || $game['stage'] !== 'play') {
				return;
			}
			$card = null;
			foreach ($game['hands']['player'] as $handCard) {
				if ($handCard['id'] === ($post['card_id'] ?? '')) {
					$card = $handCard;
					break;
				}
			}
			if (!$card) {
				return;
			}
			$effect = $card;
			if ($effect instanceof ShanCard || ($effect->isAttack() && $game['attack_used'])) {
				return;
			}
			if ($effect instanceof TaoCard && $game['health']['player'] >= 5) {
				return;
			}
			$card = removeGameCard($game, 'player', $card['id']);
			if (!$card instanceof PlayingCard) {
				return;
			}
			if ($effect->isAttack()) {
				$game['attack_used'] = true;
				$game['discard'][] = $card;
				$flashIndex = findGameCardIndex($game['hands']['bot'], ShanCard::class);
				if ($flashIndex !== null) {
					$flash = array_splice($game['hands']['bot'], $flashIndex, 1)[0];
					$game['discard'][] = $flash;
					addGameLog($game, '你使用' . $effect->name() . '；Bot 打出閃避，攻擊無效。');
				} else {
				addGameLog($game, '你使用' . $effect->name() . '。');
				damageGameSide($game, 'bot', $effect->damage());
				}
				return;
			}
			if ($effect instanceof TaoCard) {
				$game['health']['player'] = min(5, $game['health']['player'] + 1);
				$game['discard'][] = $card;
				addGameLog($game, '你使用桃，回覆 1 點生命。');
				return;
			}

			if ($effect instanceof TrickCardEffect) {
				$trickEffect = $effect;
				if (!$trickEffect->needsTarget() || !$game['hands']['bot']) {
					$result = $trickEffect->apply($game, 'player');
					addGameLog($game, $result['log']);
					return;
				}

				$game['pending_target'] = ['card' => $card];
				$game['stage'] = 'choose_target';
				return;
			}
			return;

		case 'choose_target':
			if ($game['turn'] !== 'player' || $game['stage'] !== 'choose_target' || !$game['pending_target']) {
				return;
			}
			$targetIndex = filter_var($post['target_index'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]);
			if ($targetIndex === false || !isset($game['hands']['bot'][$targetIndex])) {
				return;
			}
			$pending = $game['pending_target'];
			$result = $pending['card']->apply($game, 'player', $targetIndex);
			addGameLog($game, $result['log']);
			$game['pending_target'] = null;
			$game['stage'] = 'play';
			return;

		case 'cancel_target':
			if ($game['stage'] === 'choose_target' && $game['pending_target']) {
				$game['hands']['player'][] = $game['pending_target']['card'];
				$game['pending_target'] = null;
				$game['stage'] = 'play';
			}
			return;

		case 'end_play':
			if ($game['turn'] !== 'player' || $game['stage'] !== 'play') {
				return;
			}
			if (count($game['hands']['player']) > $game['health']['player']) {
				$game['stage'] = 'discard';
				addGameLog($game, '請棄置手牌，直到手牌數不高於生命值。');
			} else {
				finishPlayerTurn($game);
			}
			return;

		case 'deposit_card':
			if ($game['turn'] !== 'player' || $game['stage'] !== 'discard' || count($game['hands']['player']) <= $game['health']['player']) {
				return;
			}
			$card = removeGameCard($game, 'player', (string) ($post['card_id'] ?? ''));
			if (!$card) {
				return;
			}
			$game['discard'][] = $card;
			addGameLog($game, '你棄置 ' . $card['name'] . $card['label'] . '。');
			if (count($game['hands']['player']) <= $game['health']['player']) {
				finishPlayerTurn($game);
			}
			return;

		case 'respond_flash':
			if ($game['stage'] !== 'response' || !$game['response']) {
				return;
			}
			$flashIndex = null;
			foreach ($game['hands']['player'] as $index => $card) {
				if ($card['id'] === (string) ($post['card_id'] ?? '') && $card instanceof ShanCard) {
					$flashIndex = $index;
					break;
				}
			}
			if ($flashIndex === null) {
				return;
			}
			$flash = array_splice($game['hands']['player'], $flashIndex, 1)[0];
			$game['discard'][] = $flash;
			$game['response']['received_dodges'] = ($game['response']['received_dodges'] ?? 0) + 1;
			$requiredDodges = $game['response']['required_dodges'] ?? 1;
			if ($game['response']['received_dodges'] < $requiredDodges) {
				$remainingDodges = $requiredDodges - $game['response']['received_dodges'];
				addGameLog($game, '你打出閃，還需要 ' . $remainingDodges . ' 張。');
				return;
			}

			$game['discard'][] = $game['response']['card'];
			$usedDodges = $game['response']['received_dodges'];
			$game['response'] = null;
			addGameLog($game, '你打出 ' . $usedDodges . ' 張閃，Bot 的攻擊無效。');
			finishBotTurn($game);
			return;

		case 'take_damage':
			if ($game['stage'] !== 'response' || !$game['response']) {
				return;
			}
			$response = $game['response'];
			$game['discard'][] = $response['card'];
			$game['response'] = null;
			damageGameSide($game, 'player', $response['damage']);
			finishBotTurn($game);
			return;
	}
}

function renderGameCard(PlayingCard $card, string $token, string $action = '', string $actionLabel = '', string $fieldName = 'card_id'): string
{
	$effect = $card;
	$classes = 'playing-card playing-card--' . $card['color'];
	$label = $card['name'] . $card['label'] . '，' . $effect->name();
	$body = '<span class="card-corner card-corner--top" aria-hidden="true"><span>' . gameEscape($card['label']) . '</span><span>' . gameEscape($card['symbol']) . '</span></span>'
		. '<span class="card-effect">' . gameEscape($effect->name()) . '</span>'
		. '<span class="card-corner card-corner--bottom" aria-hidden="true"><span>' . gameEscape($card['label']) . '</span><span>' . gameEscape($card['symbol']) . '</span></span>';

	if ($action === '') {
		return '<div class="' . $classes . '" aria-label="' . gameEscape($label) . '">' . $body . '</div>';
	}

	return '<form class="game-card-form" method="post">'
		. '<input type="hidden" name="csrf_token" value="' . gameEscape($token) . '">'
		. '<input type="hidden" name="action" value="' . gameEscape($action) . '">'
		. '<input type="hidden" name="' . gameEscape($fieldName) . '" value="' . gameEscape($card['id']) . '">'
		. '<button class="' . $classes . ' playing-card--selectable" type="submit" aria-label="' . gameEscape($actionLabel . '：' . $label) . '" title="' . gameEscape($actionLabel) . '">' . $body . '</button>'
		. '<span class="card-action-label">' . gameEscape($actionLabel) . '</span></form>';
}

function renderCardBack(string $token, ?int $targetIndex = null, string $ariaLabel = 'Bot 手牌，背面朝上'): string
{
	$back = '<span class="card-back-mark" aria-hidden="true">三國</span>';
	if ($targetIndex === null) {
		return '<div class="playing-card card-back" role="img" aria-label="' . gameEscape($ariaLabel) . '">' . $back . '</div>';
	}

	return '<form class="game-card-form" method="post">'
		. '<input type="hidden" name="csrf_token" value="' . gameEscape($token) . '">'
		. '<input type="hidden" name="action" value="choose_target">'
		. '<input type="hidden" name="target_index" value="' . $targetIndex . '">'
		. '<button class="playing-card card-back playing-card--selectable" type="submit" aria-label="選擇 Bot 第 ' . ($targetIndex + 1) . ' 張背面手牌">' . $back . '</button>'
		. '<span class="card-action-label">選擇</span></form>';
}

if (defined('SANGUOGA_MOREBOT_BOOTSTRAP')) {
	return;
}

$_SESSION['game_token'] ??= bin2hex(random_bytes(24));
if (!isset($_SESSION['game'])
	|| ($_SESSION['game']['card_model_version'] ?? null) !== 2
	|| !($_SESSION['game']['deck'] instanceof \SplQueue)
) {
	$_SESSION['game'] = createGame();
}
$_SESSION['game']['bot_messages'] ??= [];
$_SESSION['game']['check_deck'] ??= [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	if (hash_equals($_SESSION['game_token'], (string) ($_POST['csrf_token'] ?? ''))) {
		$game = $_SESSION['game'];
		handleGameAction($game, (string) ($_POST['action'] ?? ''), $_POST);
		$_SESSION['game'] = $game;
	}
	header('Location: h2p.php');
	exit;
}

$game = $_SESSION['game'];
$token = $_SESSION['game_token'];
$stageLabels = [
	'take' => '摸牌階段',
	'play' => '出牌階段',
	'choose_target' => '選擇目標',
	'discard' => '棄牌階段',
	'response' => '閃避回應',
	'game_over' => '對局結束',
];
$stageLabel = $stageLabels[$game['stage']];
$instruction = match ($game['stage']) {
	'take' => '本回合檢查已完成。你可以從牌堆抽最多 2 張牌。',
	'play' => '可出任意張功能牌，並且每回合最多出 1 張殺。閃只能用來響應殺。',
	'choose_target' => '選擇一張背面朝上的 Bot 手牌作為目標，或取消這次出牌。',
	'discard' => '選擇手牌棄置，直到手牌數不高於你的生命值。',
	'response' => 'Bot 使用' . ($game['response']['effect'] ?? '殺') . '；已出 ' . ($game['response']['received_dodges'] ?? 0) . ' / ' . ($game['response']['required_dodges'] ?? 1) . ' 張閃，可繼續閃避或承受 ' . ($game['response']['damage'] ?? 0) . ' 點傷害。',
	'game_over' => $game['winner'] === 'player' ? '你贏了！' : '你輸了。',
	default => '',
};
?>
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>與機器對戰 | 三國大亂鬥</title>
	<link rel="stylesheet" href="css/styles.css">
</head>
<body>
	<nav class="main-nav" aria-label="主導覽">
		<ul class="left-menu">
			<li><a href="index.php">主頁</a></li>
			<li><a href="h2h.php">與人對戰</a></li>
			<li><a href="h2p.php" aria-current="page">與機器對戰</a></li>
			<li><a href="morebot.php">你 vs 3 Bot</a></li>
		</ul>
	</nav>

	<main class="computer-game computer-game--table">
		<header class="game-heading game-heading--compact">
			<p class="game-eyebrow">三國大亂鬥</p>
			<h1>與機器對戰</h1>
		</header>

		<div class="game-board">
			<section class="hand-panel" aria-labelledby="player-title">
				<header class="hand-panel-heading">
					<div><p class="game-eyebrow">玩家</p><h2 id="player-title">你</h2></div>
					<p class="health-count">生命 <strong><?= $game['health']['player'] ?></strong> / 5</p>
				</header>
				<div class="hand-with-delayed">
					<div class="card-grid" aria-label="你的手牌">
						<?php foreach ($game['hands']['player'] as $card): ?>
							<?php
							$effect = $card;
						$cardAction = '';
					$actionLabel = '';
					if ($game['stage'] === 'play' && !($effect instanceof ShanCard) && (!$effect->isAttack() || !$game['attack_used']) && (!($effect instanceof TaoCard) || $game['health']['player'] < 5)) {
							$cardAction = 'play_card';
							$actionLabel = '打出';
						} elseif ($game['stage'] === 'discard') {
							$cardAction = 'deposit_card';
							$actionLabel = '棄置';
						} elseif ($game['stage'] === 'response' && $effect instanceof ShanCard) {
							$cardAction = 'respond_flash';
							$actionLabel = '打出閃';
						}
						echo renderGameCard($card, $token, $cardAction, $actionLabel);
							?>
						<?php endforeach; ?>
					</div>
					<aside class="delayed-panel" aria-label="你身上的延遲判定">
						<h3>延遲判定</h3>
						<?php foreach ($game['delayed']['player'] as $card): ?>
							<?= renderGameCard($card, $token) ?>
						<?php endforeach; ?>
						<?php if (!$game['delayed']['player']): ?><p>無</p><?php endif; ?>
					</aside>
				</div>
			</section>

			<section class="table-zone" aria-label="牌堆與回合操作">
				<div class="piles">
					<div class="pile">
						<h2>抽牌堆</h2>
						<?php if (!$game['deck']->isEmpty()): ?>
							<?= renderCardBack($token, null, '抽牌堆，牌面朝下') ?>
						<?php else: ?>
							<div class="empty-pile">牌堆已空</div>
						<?php endif; ?>
						<p>剩餘 <?= count($game['deck']) ?> 張</p>
					</div>
					<div class="pile">
						<h2>棄置牌堆</h2>
						<?php if ($game['discard']): ?>
							<?= renderGameCard($game['discard'][count($game['discard']) - 1], $token) ?>
						<?php else: ?>
							<div class="empty-pile">尚無棄牌</div>
						<?php endif; ?>
						<p>共 <?= count($game['discard']) ?> 張</p>
						<?php if ($game['bot_messages']): ?>
							<ul class="bot-action-messages" aria-label="Bot 行動說明">
								<?php foreach ($game['bot_messages'] as $message): ?><li><?= gameEscape($message) ?></li><?php endforeach; ?>
							</ul>
						<?php endif; ?>
					</div>
					<div class="pile">
						<h2>Check deck</h2>
						<?php if ($game['check_deck']): ?>
							<?= renderCardBack($token, null, 'Check deck，牌面朝下') ?>
						<?php else: ?>
							<div class="empty-pile">尚無判定牌</div>
						<?php endif; ?>
						<p>共 <?= count($game['check_deck']) ?> 張</p>
					</div>
					<?php if ($game['response']): ?>
						<div class="pending-attack">
							<h2>待響應攻擊</h2>
							<?= renderGameCard($game['response']['card'], $token) ?>
						</div>
					<?php endif; ?>
				</div>

				<div class="stage-panel" aria-live="polite">
					<p class="stage-kicker"><?= gameEscape($game['turn'] === 'player' ? '你的回合' : 'Bot 回合') ?></p>
					<h2><?= gameEscape($stageLabel) ?></h2>
					<p><?= gameEscape($instruction) ?></p>
					<?php if ($game['stage'] === 'take'): ?>
						<form method="post"><input type="hidden" name="csrf_token" value="<?= gameEscape($token) ?>"><input type="hidden" name="action" value="take"><button class="game-button game-button--primary" type="submit">摸兩張</button></form>
					<?php elseif ($game['stage'] === 'play'): ?>
						<form method="post"><input type="hidden" name="csrf_token" value="<?= gameEscape($token) ?>"><input type="hidden" name="action" value="end_play"><button class="game-button" type="submit">結束出牌</button></form>
					<?php elseif ($game['stage'] === 'choose_target'): ?>
						<form method="post"><input type="hidden" name="csrf_token" value="<?= gameEscape($token) ?>"><input type="hidden" name="action" value="cancel_target"><button class="game-button" type="submit">取消出牌</button></form>
					<?php elseif ($game['stage'] === 'response'): ?>
						<form method="post"><input type="hidden" name="csrf_token" value="<?= gameEscape($token) ?>"><input type="hidden" name="action" value="take_damage"><button class="game-button" type="submit">不出閃，承受傷害</button></form>
					<?php elseif ($game['stage'] === 'game_over'): ?>
						<form method="post"><input type="hidden" name="csrf_token" value="<?= gameEscape($token) ?>"><input type="hidden" name="action" value="new_game"><button class="game-button game-button--primary" type="submit">再玩一局</button></form>
					<?php endif; ?>
				</div>
			</section>

			<section class="hand-panel bot-panel" aria-labelledby="bot-title">
				<header class="hand-panel-heading">
					<div><p class="game-eyebrow">對手</p><h2 id="bot-title">Bot</h2></div>
					<p class="health-count">生命 <strong><?= $game['health']['bot'] ?></strong> / 5</p>
				</header>
				<div class="hand-with-delayed">
					<div class="card-grid" aria-label="Bot 手牌，牌面朝下">
						<?php foreach ($game['hands']['bot'] as $index => $card): ?>
							<?= renderCardBack($token, $game['stage'] === 'choose_target' ? $index : null) ?>
						<?php endforeach; ?>
					</div>
					<aside class="delayed-panel" aria-label="Bot 身上的延遲判定">
						<h3>延遲判定</h3>
						<?php foreach ($game['delayed']['bot'] as $card): ?>
							<?= renderGameCard($card, $token) ?>
						<?php endforeach; ?>
						<?php if (!$game['delayed']['bot']): ?><p>無</p><?php endif; ?>
					</aside>
				</div>
			</section>
		</div>

		<section class="game-log" aria-label="對局紀錄" aria-live="polite">
			<h2>對局紀錄</h2>
			<ol><?php foreach ($game['log'] as $entry): ?><li><?= gameEscape($entry) ?></li><?php endforeach; ?></ol>
		</section>

		<form class="restart-form" method="post"><input type="hidden" name="csrf_token" value="<?= gameEscape($token) ?>"><input type="hidden" name="action" value="new_game"><button class="text-button" type="submit">重新開始</button></form>
	</main>
</body>
</html>
