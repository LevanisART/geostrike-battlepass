<?php

class LevelsComponent {
    public static function render($rewardData, $currentLevel, $takenLevels, $user, $Translate) {
        ob_start();
        ?>
        <div class="rewards-track">
            <?php
            // Determine max level - either from reward data or default to 15
            $maxLevel = !empty($rewardData) ? max(array_keys($rewardData)) : 15;
            $maxLevel = max($maxLevel, 15); // Ensure minimum 15 levels
            
            for ($level = 1; $level <= $maxLevel; $level++):
                // Determine reward info
                $rewardInfo = $rewardData[$level] ?? null;
                $stars = 0;
                
                if ($rewardInfo) {
                    $stars = $rewardInfo['starslvl'] ?? $rewardInfo['stars'] ?? 0;
                } else {
                    // Default star pattern for levels without data
                    if ($level == 2) $stars = 1;
                    elseif ($level == 5) $stars = 2;
                    elseif ($level == 9 || $level == 10) $stars = 1;
                    elseif ($level == 14) $stars = 3;
                }
                
                // Determine level state
                $stateClass = '';
                $isTaken = in_array($level, $takenLevels);
                
                if ($isTaken) {
                    $stateClass = 'earned';
                } elseif ($level == $currentLevel) {
                    $stateClass = 'current';
                } elseif ($level < $currentLevel) {
                    $stateClass = 'earned';
                }
                ?>
                <div class="reward-level-item <?php echo $stateClass; ?>" data-level="<?php echo $level; ?>">
                    <div class="level-number"><?php echo $level; ?></div>
                    <div class="level-label">ლეველი</div>
                    <div class="reward-xp"><?php echo $stars; ?>★</div>
                </div>
            <?php endfor; ?>
        </div>
        <?php
        return ob_get_clean();
    }
}