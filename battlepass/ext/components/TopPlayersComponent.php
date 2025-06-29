<?php

class TopPlayersComponent {
    public static function render($topPlayers, $nicknames, $Translate) {
        ob_start();
        ?>
        <div class="leaderboard-list">
            <?php foreach (array_slice($topPlayers, 0, 10) as $index => $player): ?>
                <?php 
                $rank = $index + 1;
                $rankClass = '';
                if ($rank <= 3) {
                    $rankClass = 'rank-' . $rank;
                }
                
                // Handle different data structures
                $playerName = '';
                $playerExp = '';
                $avatarUrl = 'avatar.jpg'; // default
                
                if (isset($player['name'])) {
                    // Simple test data structure
                    $playerName = htmlspecialchars($player['name']);
                    $playerExp = $player['exp'] . ' EXP';
                    $avatarUrl = $player['avatar'] ?? 'avatar.jpg';
                } elseif (isset($player['steamid'])) {
                    // Real database structure
                    $steamid = $player['steamid'];
                    $playerName = $nicknames[$steamid] ?? 'Unknown';
                    $playerExp = number_format($player['exp']) . ' EXP';
                    
                    // Try to get avatar from cache
                    $domain = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'];
                    $filePath = $_SERVER['DOCUMENT_ROOT'] . "/storage/cache/img/avatars/" . htmlspecialchars($steamid) . ".json";
                    $avatarData = file_exists($filePath) ? json_decode(file_get_contents($filePath), true) : null;
                    $avatarUrl = $avatarData['avatar'] ?? $domain . '/app/modules/module_page_battlepass/assets/img/NoImage.webp';
                } else {
                    // Fallback
                    $playerName = 'Player ' . $rank;
                    $playerExp = '0 EXP';
                }
                ?>
                <div class="leaderboard-item <?php echo $rankClass; ?>">
                    <div class="rank">#<?php echo $rank; ?></div>
                    <div class="player-avatar">
                        <img src="<?php echo htmlspecialchars($avatarUrl); ?>" alt="Player Avatar">
                    </div>
                    <div class="player-info">
                        <div class="player-name"><?php echo $playerName; ?></div>
                        <div class="player-exp"><?php echo $playerExp; ?></div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <?php
        return ob_get_clean();
    }
}