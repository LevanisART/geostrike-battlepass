<?php
// Simple component test without CoreController
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Mock the Translate class
class MockTranslate {
    public function get_translate_module_phrase($module, $phrase) {
        $translations = [
            '_AuthMsg' => 'Please login to view Battle Pass content',
            '_Auth' => 'Login'
        ];
        return $translations[$phrase] ?? $phrase;
    }
}

$Translate = new MockTranslate();

// Include the CSS (stylesheet.css first for CSS variables)
echo '<link rel="stylesheet" type="text/css" href="stylesheet.css">';
echo '<link rel="stylesheet" type="text/css" href="battlepass/style/1.css">';

// Include components
require_once 'battlepass/ext/components/SeasonBannerComponent.php';
require_once 'battlepass/ext/components/TopPlayersComponent.php';
require_once 'battlepass/ext/components/MissionsComponent.php';
require_once 'battlepass/ext/components/LevelsComponent.php';
require_once 'battlepass/ext/components/AuthMessageComponent.php';

// Mock data
$nameseason = 'Season 1';
$currentLevel = 5;
$explvl = '520/600';
$daysLeft = 30;
$usid = '76561198148128745';

$topPlayers = [
    ['name' => 'STyLa™', 'exp' => 520, 'avatar' => 'avatar.jpg'],
    ['name' => 'KatSenT', 'exp' => 490, 'avatar' => 'avatar.jpg'],
    ['name' => '11', 'exp' => 430, 'avatar' => 'avatar.jpg'],
    ['name' => 'TupacShak...', 'exp' => 385, 'avatar' => 'avatar.jpg'],
    ['name' => 'Player5', 'exp' => 350, 'avatar' => 'avatar.jpg'],
];

$nicknames = [
    '76561198148128745' => 'STyLa™',
    '76561198148128746' => 'KatSenT',
    '76561198148128747' => '11',
    '76561198148128748' => 'TupacShak...',
    '76561198148128749' => 'Player5',
];

$missionData = [
    [
        'description' => '10 მოკვლა (Dual Berettas)',
        'progress' => 0,
        'target' => 10,
        'exp' => 30,
        'time_left' => '23:39:56'
    ],
    [
        'description' => 'Plant 9 Times on B',
        'progress' => 4,
        'target' => 9,
        'exp' => 55,
        'time_left' => '23:59:56'
    ],
    [
        'description' => '5 Wallbang Kills (SG 553)',
        'progress' => 0,
        'target' => 5,
        'exp' => 45,
        'time_left' => '23:59:56'
    ]
];

$rewardData = [
    1 => ['type' => 'weapon', 'name' => 'AK-47 Redline', 'image' => 'avatar.jpg'],
    2 => ['type' => 'exp', 'amount' => 50],
    3 => ['type' => 'weapon', 'name' => 'AWP Dragon Lore', 'image' => 'avatar.jpg'],
    4 => ['type' => 'exp', 'amount' => 75],
    5 => ['type' => 'weapon', 'name' => 'M4A4 Howl', 'image' => 'avatar.jpg'],
    6 => ['type' => 'exp', 'amount' => 100],
    7 => ['type' => 'weapon', 'name' => 'Glock Fade', 'image' => 'avatar.jpg'],
    8 => ['type' => 'exp', 'amount' => 125],
    9 => ['type' => 'weapon', 'name' => 'Knife Karambit', 'image' => 'avatar.jpg'],
    10 => ['type' => 'exp', 'amount' => 150],
    11 => ['type' => 'weapon', 'name' => 'USP-S Kill Confirmed', 'image' => 'avatar.jpg'],
    12 => ['type' => 'exp', 'amount' => 200],
    13 => ['type' => 'weapon', 'name' => 'AK-47 Fire Serpent', 'image' => 'avatar.jpg'],
];

$takenLevels = [1, 2, 3, 4];
$user = ['name' => 'TestUser', 'level' => 5];

echo '<html>';
echo '<head>';
echo '<meta charset="utf-8">';
echo '<meta name="viewport" content="width=device-width, initial-scale=1">';
echo '<title>Battle Pass Component Test</title>';
echo '<style>body { background: var(--fon, #101112); font-family: "Noto Sans Georgian", sans-serif; padding: 20px; }</style>';
echo '</head>';
echo '<body>';
echo '<h1 style="color: white; text-align: center;">Battle Pass Components Test</h1>';

try {
    // Main container
    echo '<div class="battlepass-container">';
    
    // Header
    echo SeasonBannerComponent::render($nameseason, $currentLevel, $explvl, $daysLeft, $Translate);
    
    // Content grid
    echo '<div class="battlepass-content-grid">';
    
    // Left: Missions
    echo '<div class="battlepass-missions-section">';
    echo '<div class="section-header">';
    echo '<h3>ხელმისაწვდომი მისიები</h3>';
    echo '<span class="user-id">UserID: ' . $usid . '</span>';
    echo '</div>';
    echo MissionsComponent::render($missionData, $usid, $Translate);
    echo '</div>';
    
    // Right: Leaderboard
    echo '<div class="battlepass-leaderboard-section">';
    echo '<div class="section-header">';
    echo '<h3>TOP 10 მოთამაშე</h3>';
    echo '</div>';
    echo TopPlayersComponent::render($topPlayers, $nicknames, $Translate);
    echo '</div>';
    
    echo '</div>'; // End content grid
    
    // Bottom: Rewards
    echo LevelsComponent::render($rewardData, $currentLevel, $takenLevels, $user, $Translate);
    
    echo '</div>'; // End container
    
    echo '<p style="color: #00ff00; text-align: center; margin-top: 20px;">✅ Battle Pass components loaded successfully!</p>';
    
} catch (Error $e) {
    echo '<div style="color: red; background: white; padding: 20px; margin: 20px; border-radius: 5px;">';
    echo '<h2>Error occurred:</h2>';
    echo '<p><strong>Message:</strong> ' . htmlspecialchars($e->getMessage()) . '</p>';
    echo '<p><strong>File:</strong> ' . htmlspecialchars($e->getFile()) . '</p>';
    echo '<p><strong>Line:</strong> ' . $e->getLine() . '</p>';
    echo '</div>';
} catch (Exception $e) {
    echo '<div style="color: red; background: white; padding: 20px; margin: 20px; border-radius: 5px;">';
    echo '<h2>Exception occurred:</h2>';
    echo '<p><strong>Message:</strong> ' . htmlspecialchars($e->getMessage()) . '</p>';
    echo '</div>';
}

echo '</body>';
echo '</html>';
?> 