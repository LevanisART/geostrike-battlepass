<?php
// Simple test file for the battlepass module
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Mock the required dependencies
class MockDb {
    public function query($sql) {
        return ['result' => true, 'data' => []];
    }
    
    public function select($table, $conditions = []) {
        return [];
    }
    
    public function insert($table, $data) {
        return true;
    }
    
    public function update($table, $data, $conditions) {
        return true;
    }
    
    public function escape($string) {
        return addslashes($string);
    }
    
    public function real_escape_string($string) {
        return addslashes($string);
    }
}

class MockGeneral {
    public function getTimeAgo($timestamp) {
        return '2 hours ago';
    }
    
    public function get_default_url_section() {
        return 'battlepass';
    }
    
    public function getUserData($steamid) {
        return [
            'name' => 'TestUser',
            'level' => 5,
            'exp' => 520,
            'avatar' => 'avatar.jpg'
        ];
    }
    
    public function formatTime($timestamp) {
        return date('Y-m-d H:i:s', $timestamp);
    }
    
    public function getServerData($group) {
        return ['name' => 'Test Server'];
    }
}

class MockTranslate {
    public function get_translate_module_phrase($module, $phrase) {
        $translations = [
            '_AuthMsg' => 'Please login to view Battle Pass content',
            '_Auth' => 'Login'
        ];
        return $translations[$phrase] ?? $phrase;
    }
}

class MockModules {
    public function isModuleEnabled($module) {
        return true;
    }
    
    public function getModuleConfig($module) {
        return [];
    }
    
    public function hasPermission($permission) {
        return true;
    }
}

// Mock session data
$_SESSION['steamid64'] = '76561198148128745'; // Example SteamID

// Create mock objects
$Db = new MockDb();
$General = new MockGeneral();
$Translate = new MockTranslate();
$Modules = new MockModules();

// Include the CSS first
echo '<link rel="stylesheet" type="text/css" href="stylesheet.css">';
echo '<link rel="stylesheet" type="text/css" href="battlepass/style/1.css">';

// Include the battlepass module with error handling
try {
    require_once 'battlepass/ext/CoreController.php';
} catch (Error $e) {
    die("Error loading CoreController: " . $e->getMessage());
}

// Mock data for testing
$mockData = [
    'nameseason' => 'Season 1',
    'currentLevel' => 5,
    'explvl' => '520/600',
    'daysLeft' => 30,
    'usid' => $_SESSION['steamid64'],
    'user' => ['name' => 'TestUser', 'level' => 5],
    'topPlayers' => [
        ['name' => 'STyLa™', 'exp' => 520, 'avatar' => 'avatar.jpg'],
        ['name' => 'KatSenT', 'exp' => 490, 'avatar' => 'avatar.jpg'],
        ['name' => '11', 'exp' => 430, 'avatar' => 'avatar.jpg'],
        ['name' => 'TupacShak...', 'exp' => 385, 'avatar' => 'avatar.jpg'],
        ['name' => 'Player5', 'exp' => 350, 'avatar' => 'avatar.jpg'],
    ],
    'nicknames' => [
        '76561198148128745' => 'STyLa™',
        '76561198148128746' => 'KatSenT',
        '76561198148128747' => '11',
        '76561198148128748' => 'TupacShak...',
        '76561198148128749' => 'Player5',
    ],
    'missionData' => [
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
    ],
    'rewardData' => [
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
    ],
    'takenLevels' => [1, 2, 3, 4] // Levels already claimed
];

echo '<html>';
echo '<head>';
echo '<meta charset="utf-8">';
echo '<meta name="viewport" content="width=device-width, initial-scale=1">';
echo '<title>Battle Pass Test</title>';
echo '<link rel="stylesheet" type="text/css" href="stylesheet.css">';
echo '<link rel="stylesheet" type="text/css" href="battlepass/style/1.css">';
echo '<style>body { background: var(--fon, #101112); font-family: "Noto Sans Georgian", sans-serif; padding: 20px; }</style>';
echo '<style id="import" data-present="1">
      :root {
        --fon: #101112;
        --knopka: #0077ff;
        --news: #13141580;
        --cp: #ffffff80;
        --cps: #0077ff;
        --lk: #131415;
        --tablcolor: linear-gradient(25deg, var(--span-color) -150%, var(--fon) 50%);
        --profgg: #131415;
        --profgg1: #131415;
        --magaz: #0077ff;
        --color-default: #fff;
        --color-dark: #ffffff80;
        --hue-rotate: hue-rotate(180deg);
        --bg-color: #2b2b2b;
        --navbar-first-color: #131415;
        --border-bottom-color: #131415;
        --border-bottom-color1: #0077ff;
        --navbar-second-color: #101112;
        --navbar-color: #131415;
        --sidebar-color: #131415;
        --sidebar-gradient-1: #0077ff;
        --sidebar-gradient-2: #0077ff;
        --button-color: #0077ff;
        --button-color-2: #0077ff;
        --button-color-3: #0077ff;
        --link-first-navbar-color: #ffffff80;
        --link-second-navbar-color: #ffffff80;
        --item-color: #ffffff80;
        --default-text-color: #fff;
        --top-text-color: #737373;
        --default-text-color-invert: #fff;
        --hover: #0077ff;
        --table-line: #0077ff;
        --svg: 100;
        --font-weight-0: 400;
        --font-weight-1: 500;
        --font-weight-2: 500;
        --font-weight-3: 600;
        --font-weight-4: 700;
        --span-color: #0077ff;
        --span-color-addit: #0077ff;
        --span-color-back: #0077ff;
        --server-graph-rgba: 10, 95, 173, 1;
        --site-glava-text: 25, 25, 26, 1;
        --site-server-groops: #0077ff;
        --site-logo-text: #0077ff
      }
    </style>';
echo '</head>';
echo '<body>';
echo '<h1 style="color: white; text-align: center;">Battle Pass Module Test</h1>';

try {
    // Create the CoreController instance
    $controller = new CoreController($Db, $General, $Translate, $Modules, 'testgroup');
    
    // Render the battle pass
    echo $controller->renderBattlePass(
        $mockData['nameseason'],
        $mockData['currentLevel'],
        $mockData['explvl'],
        $mockData['daysLeft'],
        $mockData['usid'],
        $mockData['user'],
        $mockData['topPlayers'],
        $mockData['nicknames'],
        $mockData['missionData'],
        $mockData['rewardData'],
        $mockData['takenLevels']
    );
    
} catch (Error $e) {
    echo '<div style="color: red; background: white; padding: 20px; margin: 20px; border-radius: 5px;">';
    echo '<h2>Error occurred:</h2>';
    echo '<p><strong>Message:</strong> ' . htmlspecialchars($e->getMessage()) . '</p>';
    echo '<p><strong>File:</strong> ' . htmlspecialchars($e->getFile()) . '</p>';
    echo '<p><strong>Line:</strong> ' . $e->getLine() . '</p>';
    echo '<h3>Stack Trace:</h3>';
    echo '<pre>' . htmlspecialchars($e->getTraceAsString()) . '</pre>';
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