<?php
use Sanguoga\Cards\CardEffect;
use Sanguoga\Cards\GuoheChaiqiaoCard;
use Sanguoga\Cards\LebusishuCard;
use Sanguoga\Cards\ShaCard;
use Sanguoga\Cards\ShanCard;
use Sanguoga\Cards\ShunshouQianyangCard;
use Sanguoga\Cards\TrickCardEffect;

require_once __DIR__ . '/卡牌/普通/殺招式.php';
require_once __DIR__ . '/卡牌/普通/閃.php';
require_once __DIR__ . '/卡牌/錦囊/順手牽羊.php';
require_once __DIR__ . '/卡牌/錦囊/過河拆橋.php';
require_once __DIR__ . '/卡牌/錦囊/樂不思蜀.php';

session_start();

$suits = [
	['id' => 'spades', 'name' => '黑桃', 'symbol' => '♠', 'color' => 'black'],
	['id' => 'clubs', 'name' => '梅花', 'symbol' => '♣', 'color' => 'black'],
	['id' => 'hearts', 'name' => '紅心', 'symbol' => '♥', 'color' => 'red'],
	['id' => 'diamonds', 'name' => '方塊', 'symbol' => '♦', 'color' => 'red'],
];

$ranks = [
	['label' => 'A', 'value' => 14], ['label' => '2', 'value' => 2],
	['label' => '3', 'value' => 3], ['label' => '4', 'value' => 4],
	['label' => '5', 'value' => 5], ['label' => '6', 'value' => 6],
	['label' => '7', 'value' => 7], ['label' => '8', 'value' => 8],
	['label' => '9', 'value' => 9], ['label' => '10', 'value' => 10],
	['label' => 'J', 'value' => 11], ['label' => 'Q', 'value' => 12],
	['label' => 'K', 'value' => 13],
];

function gameCardEffect(array $card): CardEffect
{
	if ($card['value'] % 7 === 0) {
		if ($card['value'] === 14) {
			return new LebusishuCard();
		}

		return $card['color'] === 'red' ? new GuoheChaiqiaoCard() : new ShunshouQianyangCard();
	}
	if ($card['value'] % 4 === 0) {
		return new ShaCard('雷殺');
	}
	if ($card['value'] % 3 === 0) {
		return new ShaCard('火殺');
	}

	return $card['color'] === 'black' ? new ShanCard() : new ShaCard();
}

function requireTrickCardEffect(CardEffect $effect): TrickCardEffect
{
	if (!$effect instanceof TrickCardEffect) {
		throw new LogicException('Expected a trick-card effect.');
	}

	return $effect;
}

function gameEscape(string $value): string
{
	return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function addGameLog(array &$game, string $message): void
{
	array_unshift($game['log'], $message);
	$game['log'] = array_slice($game['log'], 0, 10);
}

function createGame(array $suits, array $ranks): array
{
	$deck = [];
	foreach ($suits as $suit) {
		foreach ($ranks as $rank) {
			$deck[] = array_merge($suit, $rank, ['id' => $suit['id'] . '-' . $rank['label']]);
		}
	}
	shuffle($deck);

	$game = [
		'hands' => [
			'player' => array_splice($deck, 0, 4),
			'bot' => array_splice($deck, 0, 4),
		],
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
		$revealed = array_pop($game['deck']);
		if (!$revealed) {
			addGameLog($game, $sideName . '的樂不思蜀沒有牌可翻，判定結束。');
			continue;
		}

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
	while ($drawn < $amount && $game['deck']) {
		$game['hands'][$side][] = array_pop($game['deck']);
		$drawn++;
	}
	addGameLog($game, ($side === 'player' ? '你' : 'Bot') . '從牌堆抽取 ' . $drawn . ' 張牌。');

	return $drawn;
}

function findGameCardIndex(array $hand, string $effectClass): ?int
{
	foreach ($hand as $index => $card) {
		if (gameCardEffect($card) instanceof $effectClass) {
			return $index;
		}
	}

	return null;
}

function removeGameCard(array &$game, string $side, string $cardId): ?array
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

	while (true) {
		$specialIndex = null;
		foreach ($game['hands']['bot'] as $index => $card) {
			if (gameCardEffect($card) instanceof TrickCardEffect) {
				$specialIndex = $index;
				break;
			}
		}
		if ($specialIndex === null) {
			break;
		}

		$card = array_splice($game['hands']['bot'], $specialIndex, 1)[0];
		$effect = requireTrickCardEffect(gameCardEffect($card));
		$targetIndex = $effect->needsTarget() && $game['hands']['player']
			? array_rand($game['hands']['player'])
			: null;
		$result = $effect->apply($game, $card, 'bot', $targetIndex);
		if (isset($result['bot_message'])) {
			$game['bot_messages'][] = $result['bot_message'];
		}
		addGameLog($game, $result['log']);
	}

	$attackIndex = null;
	foreach ($game['hands']['bot'] as $index => $card) {
		if (gameCardEffect($card)->isAttack()) {
			$attackIndex = $index;
			break;
		}
	}
	if ($attackIndex !== null) {
		$attack = array_splice($game['hands']['bot'], $attackIndex, 1)[0];
		$effect = gameCardEffect($attack);
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
		global $suits, $ranks;
		$game = createGame($suits, $ranks);
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
			$effect = gameCardEffect($card);
			if ($effect instanceof ShanCard || ($effect->isAttack() && $game['attack_used'])) {
				return;
			}
			$card = removeGameCard($game, 'player', $card['id']);
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

			if ($effect instanceof TrickCardEffect) {
				$trickEffect = requireTrickCardEffect($effect);
				if (!$trickEffect->needsTarget() || !$game['hands']['bot']) {
					$result = $trickEffect->apply($game, $card, 'player');
					addGameLog($game, $result['log']);
					return;
				}

				$game['pending_target'] = ['card' => $card, 'effect' => $trickEffect::class];
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
			$effectClass = $pending['effect'];
			$effect = new $effectClass();
			$result = $effect->apply($game, $pending['card'], 'player', $targetIndex);
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
				if ($card['id'] === (string) ($post['card_id'] ?? '') && gameCardEffect($card) instanceof ShanCard) {
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

function renderGameCard(array $card, string $token, string $action = '', string $actionLabel = '', string $fieldName = 'card_id'): string
{
	$effect = gameCardEffect($card);
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

$_SESSION['game_token'] ??= bin2hex(random_bytes(24));
if (!isset($_SESSION['game'])) {
	$_SESSION['game'] = createGame($suits, $ranks);
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
							$effect = gameCardEffect($card);
						$cardAction = '';
					$actionLabel = '';
					if ($game['stage'] === 'play' && !($effect instanceof ShanCard) && (!$effect->isAttack() || !$game['attack_used'])) {
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
						<?php if ($game['deck']): ?>
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
