<?php
use Sanguoga\Cards\PlayingCard;
use Sanguoga\Cards\ShaCard;
use Sanguoga\Cards\ShanCard;
use Sanguoga\Cards\TaoCard;
use Sanguoga\Cards\TrickCardEffect;

define('SANGUOGA_MOREBOT_BOOTSTRAP', true);
require_once __DIR__ . '/h2p.php';

function moreBotSeats(): array
{
	return [
		'player' => '你',
		'bot1' => 'Bot 1',
		'bot2' => 'Bot 2',
		'bot3' => 'Bot 3',
	];
}

function moreBotAlive(array $game, string $side): bool
{
	return $game['health'][$side] > 0;
}

function moreBotNextOpponent(array $game, string $side): ?string
{
	$order = array_keys(moreBotSeats());
	$start = array_search($side, $order, true);
	if ($start === false) {
		return null;
	}

	for ($step = 1; $step < count($order); $step++) {
		$target = $order[($start + $step) % count($order)];
		if (moreBotAlive($game, $target)) {
			return $target;
		}
	}

	return null;
}

function moreBotCheckWinner(array &$game): void
{
	$alive = array_filter(array_keys(moreBotSeats()), static fn (string $side): bool => moreBotAlive($game, $side));
	if (count($alive) > 1) {
		return;
	}

	$game['winner'] = $alive ? reset($alive) : null;
	$game['stage'] = 'game_over';
	$winnerName = $game['winner'] === null ? '無人' : moreBotSeats()[$game['winner']];
	addGameLog($game, $winnerName . '成為最後存活的玩家。');
}

function createMoreBotGame(): array
{
	$seedGame = createGame();
	$cards = array_merge(
		$seedGame['hands']['player'],
		$seedGame['hands']['bot'],
		iterator_to_array($seedGame['deck'], false)
	);
	shuffle($cards);
	$deck = new \SplQueue();
	foreach ($cards as $card) {
		$deck->enqueue($card);
	}

	$hands = [];
	foreach (array_keys(moreBotSeats()) as $side) {
		$hands[$side] = [];
		for ($drawn = 0; $drawn < 4; $drawn++) {
			$hands[$side][] = $deck->dequeue();
		}
	}

	$game = [
		'model_version' => 7,
		'hands' => $hands,
		'health' => array_fill_keys(array_keys(moreBotSeats()), 5),
		'delayed' => array_fill_keys(array_keys(moreBotSeats()), []),
		'deck' => $deck,
		'discard' => [],
		'log' => [],
		'bot_progress' => [],
		'turn' => 'player',
		'stage' => 'take',
		'skip_play' => false,
		'attack_used' => false,
		'pending_target' => null,
		'bot_queue' => [],
		'winner' => null,
	];
	addGameLog($game, '新對局開始：你與 3 個 Bot 各獲得 4 張牌，生命值為 5。');
	moreBotPrepareTurn($game, 'player');

	return $game;
}

function moreBotDrawCards(array &$game, string $side, int $amount): int
{
	$drawn = 0;
	while ($drawn < $amount && !$game['deck']->isEmpty()) {
		$game['hands'][$side][] = $game['deck']->dequeue();
		$drawn++;
	}
	addGameLog($game, moreBotSeats()[$side] . '抽取 ' . $drawn . ' 張牌。');

	return $drawn;
}

function moreBotResolveDelayed(array &$game, string $side): void
{
	$delayed = $game['delayed'][$side];
	$game['delayed'][$side] = [];
	foreach ($delayed as $card) {
		$game['discard'][] = $card;
		if ($game['deck']->isEmpty()) {
			addGameLog($game, moreBotSeats()[$side] . '的樂不思蜀無牌可判定。');
			continue;
		}

		$revealed = $game['deck']->dequeue();
		$game['discard'][] = $revealed;
		if ($revealed['symbol'] !== '♥') {
			$game['skip_play'] = true;
			addGameLog($game, moreBotSeats()[$side] . '翻出非紅心，跳過本回合出牌。');
		} else {
			addGameLog($game, moreBotSeats()[$side] . '翻出紅心，樂不思蜀無效。');
		}
	}
}

function moreBotDamage(array &$game, string $side, int $damage, string $source): void
{
	$game['health'][$side] = max(0, $game['health'][$side] - $damage);
	addGameLog($game, moreBotSeats()[$side] . '受到 ' . $damage . ' 點傷害，剩餘生命 ' . $game['health'][$side] . '。');
	if ($side === 'player' && $game['health'][$side] === 0) {
		$game['winner'] = $source;
		$game['stage'] = 'game_over';
		addGameLog($game, moreBotSeats()[$source] . '擊敗了你。');
		return;
	}

	moreBotCheckWinner($game);
}

function moreBotPrepareTurn(array &$game, string $side): void
{
	$game['turn'] = $side;
	$game['stage'] = $side === 'player' ? 'take' : 'bot_turn';
	$game['skip_play'] = false;
	$game['attack_used'] = false;
	if ($side === 'player') {
		moreBotResolveDelayed($game, $side);
	} else {
		$game['bot_progress'][$side] = 'delayed';
	}
}

function moreBotRunBotTurn(array &$game, string $side): bool
{
	if (!moreBotAlive($game, $side) || $game['stage'] === 'game_over') {
		return true;
	}

	while (true) {
		$phase = $game['bot_progress'][$side] ?? 'delayed';
		if ($phase === 'delayed') {
			if (!$game['delayed'][$side]) {
				$game['bot_progress'][$side] = 'draw';
				continue;
			}

			$card = array_shift($game['delayed'][$side]);
			$game['discard'][] = $card;
			if ($game['deck']->isEmpty()) {
				addGameLog($game, moreBotSeats()[$side] . '的樂不思蜀無牌可判定。');
			} else {
				$revealed = $game['deck']->dequeue();
				$game['discard'][] = $revealed;
				if ($revealed['symbol'] !== '♥') {
					$game['skip_play'] = true;
					addGameLog($game, moreBotSeats()[$side] . '翻出非紅心，跳過本回合出牌。');
				} else {
					addGameLog($game, moreBotSeats()[$side] . '翻出紅心，樂不思蜀無效。');
				}
			}
			if (!$game['delayed'][$side]) {
				$game['bot_progress'][$side] = 'draw';
			}
			return false;
		}

		if ($phase === 'draw') {
			moreBotDrawCards($game, $side, 2);
			if ($game['skip_play']) {
				addGameLog($game, moreBotSeats()[$side] . '跳過出牌階段。');
				$game['bot_progress'][$side] = 'discard';
			} else {
				$game['bot_progress'][$side] = 'tricks';
			}
			return false;
		}

		if ($phase === 'tricks') {
			$trickIndex = null;
			foreach ($game['hands'][$side] as $index => $card) {
				if ($card instanceof TrickCardEffect) {
					$trickIndex = $index;
					break;
				}
			}
			if ($trickIndex !== null) {
				$card = array_splice($game['hands'][$side], $trickIndex, 1)[0];
				$targetSide = moreBotNextOpponent($game, $side);
				$targetIndex = $targetSide !== null && $card->needsTarget() && $game['hands'][$targetSide]
					? array_rand($game['hands'][$targetSide])
					: null;
				$result = $card->apply($game, $side, $targetIndex, $targetSide);
				addGameLog($game, $result['log']);
				return false;
		}
			$game['bot_progress'][$side] = 'heal';
			continue;
		}

		if ($phase === 'heal') {
			if ($game['health'][$side] < 5) {
				$peachIndex = findGameCardIndex($game['hands'][$side], TaoCard::class);
				if ($peachIndex !== null) {
					$peach = array_splice($game['hands'][$side], $peachIndex, 1)[0];
					$game['discard'][] = $peach;
					$game['health'][$side]++;
					addGameLog($game, moreBotSeats()[$side] . '使用桃，回覆 1 點生命。');
					return false;
				}
			}
			$game['bot_progress'][$side] = 'attack';
			continue;
		}

		if ($phase === 'attack') {
			$attackIndex = null;
			foreach ($game['hands'][$side] as $index => $card) {
				if ($card instanceof ShaCard) {
					$attackIndex = $index;
					break;
				}
			}
			$targetSide = moreBotNextOpponent($game, $side);
			if ($attackIndex !== null && $targetSide !== null) {
				$attack = array_splice($game['hands'][$side], $attackIndex, 1)[0];
				$flashIndex = findGameCardIndex($game['hands'][$targetSide], ShanCard::class);
				if ($flashIndex !== null) {
					$game['discard'][] = array_splice($game['hands'][$targetSide], $flashIndex, 1)[0];
					$game['discard'][] = $attack;
					addGameLog($game, moreBotSeats()[$side] . '對' . moreBotSeats()[$targetSide] . '使用' . $attack->name() . '，遭閃避。');
				} else {
					$game['discard'][] = $attack;
					moreBotDamage($game, $targetSide, $attack->damage(), $side);
				}
			}
			$game['bot_progress'][$side] = 'discard';
			if ($attackIndex !== null && $targetSide !== null) {
				return false;
			}
			continue;
		}

		if ($phase === 'discard') {
			if ($game['stage'] !== 'game_over' && count($game['hands'][$side]) > $game['health'][$side]) {
				$index = array_rand($game['hands'][$side]);
				$card = array_splice($game['hands'][$side], $index, 1)[0];
				$game['discard'][] = $card;
				addGameLog($game, moreBotSeats()[$side] . '棄置 ' . $card['name'] . $card['label'] . '。');
				return false;
			}
			unset($game['bot_progress'][$side]);
			return true;
		}
	}
}

function moreBotAdvanceQueue(array &$game): void
{
	while ($game['bot_queue']) {
		$side = array_shift($game['bot_queue']);
		if (moreBotAlive($game, $side)) {
			moreBotPrepareTurn($game, $side);
			return;
		}
	}
	if ($game['stage'] !== 'game_over') {
		moreBotPrepareTurn($game, 'player');
	}
}

function moreBotRunBotRound(array &$game): void
{
	$game['bot_queue'] = ['bot1', 'bot2', 'bot3'];
	moreBotAdvanceQueue($game);
}

function moreBotHandleAction(array &$game, string $action, array $post): void
{
	if ($action === 'new_game') {
		$game = createMoreBotGame();
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
			moreBotDrawCards($game, 'player', 2);
			if ($game['skip_play']) {
				addGameLog($game, '你跳過出牌階段。');
				moreBotRunBotRound($game);
			} else {
				$game['stage'] = 'play';
			}
			return;

		case 'bot_step':
			if ($game['stage'] !== 'bot_turn' || !in_array($game['turn'], ['bot1', 'bot2', 'bot3'], true)) {
				return;
			}
			$turnComplete = moreBotRunBotTurn($game, $game['turn']);
			if ($turnComplete && $game['stage'] !== 'game_over') {
				moreBotAdvanceQueue($game);
			}
			return;

		case 'play_card':
			if ($game['turn'] !== 'player' || $game['stage'] !== 'play') {
				return;
			}
			$cardId = (string) ($post['card_id'] ?? '');
			$cardIndex = null;
			foreach ($game['hands']['player'] as $index => $card) {
				if ($card['id'] === $cardId) {
					$cardIndex = $index;
					break;
				}
			}
			if ($cardIndex === null) {
				return;
			}
			$card = $game['hands']['player'][$cardIndex];
			if ($card instanceof TaoCard) {
				if ($game['health']['player'] >= 5) {
					return;
				}
				array_splice($game['hands']['player'], $cardIndex, 1);
				$game['discard'][] = $card;
				$game['health']['player']++;
				addGameLog($game, '你使用桃，回覆 1 點生命。');
				return;
			}
			$targetSide = (string) ($post['target_side'] ?? '');
			if (!in_array($targetSide, ['bot1', 'bot2', 'bot3'], true) || !moreBotAlive($game, $targetSide)) {
				return;
			}
			if ($card instanceof ShanCard || ($card->isAttack() && $game['attack_used'])) {
				return;
			}
			array_splice($game['hands']['player'], $cardIndex, 1);

			if ($card instanceof ShaCard) {
				$game['attack_used'] = true;
				$flashIndex = findGameCardIndex($game['hands'][$targetSide], ShanCard::class);
				if ($flashIndex !== null) {
					$game['discard'][] = array_splice($game['hands'][$targetSide], $flashIndex, 1)[0];
					$game['discard'][] = $card;
					addGameLog($game, '你對' . moreBotSeats()[$targetSide] . '使用' . $card->name() . '，遭閃避。');
				} else {
					$game['discard'][] = $card;
					moreBotDamage($game, $targetSide, $card->damage(), 'player');
				}
				return;
			}

			if ($card instanceof TrickCardEffect) {
				if (!$card->needsTarget() || !$game['hands'][$targetSide]) {
					$result = $card->apply($game, 'player', null, $targetSide);
					addGameLog($game, $result['log']);
				} else {
					$game['pending_target'] = ['card' => $card, 'target_side' => $targetSide];
					$game['stage'] = 'choose_target';
				}
			}
			return;

		case 'choose_target':
			if ($game['stage'] !== 'choose_target' || !$game['pending_target']) {
				return;
			}
			$targetIndex = filter_var($post['target_index'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]);
			$pending = $game['pending_target'];
			$targetSide = $pending['target_side'];
			if ($targetIndex === false || !isset($game['hands'][$targetSide][$targetIndex])) {
				return;
			}
			$result = $pending['card']->apply($game, 'player', $targetIndex, $targetSide);
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
			} else {
				moreBotRunBotRound($game);
			}
			return;

		case 'deposit_card':
			if ($game['turn'] !== 'player' || $game['stage'] !== 'discard') {
				return;
			}
			$cardId = (string) ($post['card_id'] ?? '');
			foreach ($game['hands']['player'] as $index => $card) {
				if ($card['id'] === $cardId) {
					$game['discard'][] = array_splice($game['hands']['player'], $index, 1)[0];
					break;
				}
			}
			if (count($game['hands']['player']) <= $game['health']['player']) {
				moreBotRunBotRound($game);
			}
			return;
	}
}

function renderMoreBotPlayerCard(PlayingCard $card, array $game, string $token): string
{
	if ($game['stage'] === 'discard') {
		return renderGameCard($card, $token, 'deposit_card', '棄置');
	}
	if ($card instanceof TaoCard) {
		return $game['stage'] === 'play' && $game['health']['player'] < 5
			? renderGameCard($card, $token, 'play_card', '打出')
			: renderGameCard($card, $token);
	}
	if ($game['stage'] !== 'play' || $card instanceof ShanCard || ($card->isAttack() && $game['attack_used'])) {
		return renderGameCard($card, $token);
	}

	$targets = '';
	foreach (['bot1', 'bot2', 'bot3'] as $side) {
		if (moreBotAlive($game, $side)) {
			$targets .= '<option value="' . $side . '">' . gameEscape(moreBotSeats()[$side]) . '</option>';
		}
	}
	$label = $card['name'] . $card['label'] . '，' . $card->name();
	$body = '<span class="card-corner card-corner--top" aria-hidden="true"><span>' . gameEscape($card['label']) . '</span><span>' . gameEscape($card['symbol']) . '</span></span>'
		. '<span class="card-effect">' . gameEscape($card->name()) . '</span>'
		. '<span class="card-corner card-corner--bottom" aria-hidden="true"><span>' . gameEscape($card['label']) . '</span><span>' . gameEscape($card['symbol']) . '</span></span>';

	return '<form class="game-card-form multi-card-form" method="post">'
		. '<input type="hidden" name="csrf_token" value="' . gameEscape($token) . '">'
		. '<input type="hidden" name="action" value="play_card">'
		. '<input type="hidden" name="card_id" value="' . gameEscape($card['id']) . '">'
		. '<label class="multi-target-label">目標<select name="target_side">' . $targets . '</select></label>'
		. '<button class="playing-card playing-card--' . gameEscape($card['color']) . ' playing-card--selectable" type="submit" aria-label="出牌：' . gameEscape($label) . '">' . $body . '</button>'
		. '<span class="card-action-label">打出</span></form>';
}

function renderMoreBotTargetBack(string $token, int $targetIndex): string
{
	return '<form class="game-card-form" method="post">'
		. '<input type="hidden" name="csrf_token" value="' . gameEscape($token) . '">'
		. '<input type="hidden" name="action" value="choose_target">'
		. '<input type="hidden" name="target_index" value="' . $targetIndex . '">'
		. '<button class="playing-card card-back playing-card--selectable" type="submit" aria-label="選擇目標手牌">'
		. '<span class="card-back-mark" aria-hidden="true">三國</span></button>'
		. '<span class="card-action-label">選擇</span></form>';
}

$_SESSION['morebot_token'] ??= bin2hex(random_bytes(24));
if (!isset($_SESSION['morebot_game'])
	|| ($_SESSION['morebot_game']['model_version'] ?? null) !== 7
	|| !($_SESSION['morebot_game']['deck'] instanceof \SplQueue)
) {
	$_SESSION['morebot_game'] = createMoreBotGame();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST'
	&& hash_equals($_SESSION['morebot_token'], (string) ($_POST['csrf_token'] ?? ''))
) {
	$game = $_SESSION['morebot_game'];
	moreBotHandleAction($game, (string) ($_POST['action'] ?? ''), $_POST);
	$_SESSION['morebot_game'] = $game;
	header('Location: morebot.php');
	exit;
}

$game = $_SESSION['morebot_game'];
$token = $_SESSION['morebot_token'];
$labels = ['take' => '摸牌階段', 'play' => '出牌階段', 'choose_target' => '選擇目標手牌', 'discard' => '棄牌階段', 'bot_turn' => 'Bot 回合', 'game_over' => '對局結束'];
$botActionLabels = [
	'delayed' => '正在進行延遲判定',
	'draw' => '正在抽牌',
	'tricks' => '正在使用錦囊',
	'heal' => '正在使用桃回覆生命',
	'attack' => '正在選擇攻擊',
	'discard' => '正在調整手牌',
];
$stageLabel = $labels[$game['stage']] ?? 'Bot 回合';
?>
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>你 vs 3 Bot | 三國大亂鬥</title>
	<link rel="stylesheet" href="css/styles.css">
</head>
<body>
	<nav class="main-nav" aria-label="主導覽">
		<ul class="left-menu">
			<li><a href="index.php">主頁</a></li>
			<li><a href="h2h.php">與人對戰</a></li>
			<li><a href="h2p.php">與機器對戰</a></li>
			<li><a href="morebot.php" aria-current="page">你 vs 3 Bot</a></li>
		</ul>
	</nav>

	<main class="computer-game computer-game--table morebot-game">
		<header class="game-heading game-heading--compact">
			<p class="game-eyebrow">四人對局</p>
			<h1>你 vs 3 Bot</h1>
		</header>

		<div class="multi-seat-grid">
			<?php foreach (moreBotSeats() as $side => $name): ?>
				<section class="multi-seat<?= $side === 'player' ? ' multi-seat--player' : '' ?>" aria-label="<?= gameEscape($name) ?>狀態">
					<header class="hand-panel-heading">
						<div><p class="game-eyebrow"><?= $side === 'player' ? '玩家' : '對手' ?></p><h2><?= gameEscape($name) ?></h2></div>
						<p class="health-count">生命 <strong><?= $game['health'][$side] ?></strong> / 5</p>
					</header>
					<?php if ($side === 'player'): ?>
						<div class="multi-player-hand" aria-label="你的手牌">
							<?php foreach ($game['hands']['player'] as $card): ?>
								<?= renderMoreBotPlayerCard($card, $game, $token) ?>
							<?php endforeach; ?>
						</div>
					<?php else: ?>
						<div class="multi-card-backs" aria-label="<?= gameEscape($name) ?>手牌，牌面朝下">
							<?php foreach ($game['hands'][$side] as $index => $card): ?>
								<?php if ($game['stage'] === 'choose_target' && $game['pending_target']['target_side'] === $side): ?>
									<?= renderMoreBotTargetBack($token, $index) ?>
								<?php else: ?>
									<?= renderCardBack($token, null, gameEscape($name) . '手牌，牌面朝下') ?>
								<?php endif; ?>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>
					<p class="multi-seat-meta"><?= count($game['hands'][$side]) ?> 張手牌<?php if ($game['delayed'][$side]): ?> · 有延遲判定<?php endif; ?></p>
				</section>
			<?php endforeach; ?>
		</div>

		<div class="multi-table-layout">
			<section class="multi-pile-row" aria-label="牌堆資訊">
				<div class="pile"><h2>抽牌堆</h2><div class="empty-pile<?= $game['deck']->isEmpty() ? '' : ' card-back' ?>"><?= $game['deck']->isEmpty() ? '已空' : '三國' ?></div><p>剩餘 <?= count($game['deck']) ?> 張</p></div>
				<div class="pile"><h2>棄置牌堆</h2><?php if ($game['discard']): ?><?= renderGameCard($game['discard'][count($game['discard']) - 1], $token) ?><?php else: ?><div class="empty-pile">尚無棄牌</div><?php endif; ?><p>共 <?= count($game['discard']) ?> 張</p></div>
			</section>
			<section class="multi-stage-panel" aria-live="polite">
				<p class="game-eyebrow"><?= $game['turn'] === 'player' ? '你的回合' : gameEscape(moreBotSeats()[$game['turn']] ?? 'Bot') ?></p>
				<h2><?= gameEscape($stageLabel) ?></h2>
				<p><?php if ($game['stage'] === 'take'): ?>抽兩張牌開始你的回合。<?php elseif ($game['stage'] === 'play'): ?>出牌攻擊或使用錦囊；每回合最多使用一張殺。<?php elseif ($game['stage'] === 'choose_target'): ?>選擇 <?= gameEscape(moreBotSeats()[$game['pending_target']['target_side']] ?? '目標') ?> 的一張手牌。<?php elseif ($game['stage'] === 'discard'): ?>棄牌至手牌數不高於目前生命值。<?php elseif ($game['stage'] === 'game_over'): ?><?= $game['winner'] === 'player' ? '你贏了！' : gameEscape(moreBotSeats()[$game['winner']] . '獲勝。') ?><?php elseif ($game['stage'] === 'bot_turn'): ?><?= gameEscape($botActionLabels[$game['bot_progress'][$game['turn']] ?? ''] ?? 'Bot 正在思考') ?>。<?php else: ?>Bot 正在完成回合。<?php endif; ?></p>
					<?php if ($game['stage'] === 'take'): ?>
						<form method="post"><input type="hidden" name="csrf_token" value="<?= gameEscape($token) ?>"><input type="hidden" name="action" value="take"><button class="game-button game-button--primary" type="submit">摸兩張</button></form>
					<?php elseif ($game['stage'] === 'play'): ?>
						<form method="post"><input type="hidden" name="csrf_token" value="<?= gameEscape($token) ?>"><input type="hidden" name="action" value="end_play"><button class="game-button" type="submit">結束出牌</button></form>
					<?php elseif ($game['stage'] === 'choose_target'): ?>
						<form method="post"><input type="hidden" name="csrf_token" value="<?= gameEscape($token) ?>"><input type="hidden" name="action" value="cancel_target"><button class="game-button" type="submit">取消出牌</button></form>
							<?php elseif ($game['stage'] === 'bot_turn'): ?>
								<form id="bot-step-form" method="post"><input type="hidden" name="csrf_token" value="<?= gameEscape($token) ?>"><input type="hidden" name="action" value="bot_step"><button class="game-button" type="submit">繼續</button></form>
								<script>window.setTimeout(() => document.getElementById('bot-step-form')?.requestSubmit(), 1100);</script>
					<?php elseif ($game['stage'] === 'game_over'): ?>
						<form method="post"><input type="hidden" name="csrf_token" value="<?= gameEscape($token) ?>"><input type="hidden" name="action" value="new_game"><button class="game-button game-button--primary" type="submit">再玩一局</button></form>
					<?php endif; ?>
			</section>
		</div>

		<section class="game-log" aria-label="對局紀錄"><h2>對局紀錄</h2><ol><?php foreach ($game['log'] as $entry): ?><li><?= gameEscape($entry) ?></li><?php endforeach; ?></ol></section>
		<form class="restart-form" method="post"><input type="hidden" name="csrf_token" value="<?= gameEscape($token) ?>"><input type="hidden" name="action" value="new_game"><button class="text-button" type="submit">重新開始</button></form>
	</main>
</body>
</html>
