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
            
            for ($level = 1; $level <= $maxLevel; $level++) {
                // Get reward info from database
                $rewardInfo = $rewardData[$level] ?? null;
                
                // Extract free and paid rewards
                $freeStars = 0;
                $paidStars = 0;
                $freeExp = 0;
                $paidExp = 0;
                $freeMoney = 0;
                $paidMoney = 0;
                $expNeeded = 0;
                
                // Try to get data from database first
                $useTestData = true;
                if ($rewardInfo) {
                    // Try new database structure first
                    $freeStars = $rewardInfo['StarReward'] ?? 0;
                    $paidStars = $rewardInfo['StarRewardPaid'] ?? 0;
                    $freeExp = $rewardInfo['ExpReward'] ?? 0;
                    $paidExp = $rewardInfo['ExpRewardPaid'] ?? 0;
                    $freeMoney = $rewardInfo['ItemCode'] ?? 0;
                    $paidMoney = $rewardInfo['ItemCodePaid'] ?? 0;
                    $expNeeded = $rewardInfo['ExpNeeded'] ?? ($level * 100);
                    
                    // Check if we have meaningful data
                    if ($freeStars > 0 || $paidStars > 0 || $freeExp > 0 || $paidExp > 0 || $freeMoney > 0 || $paidMoney > 0) {
                        $useTestData = false;
                    } else {
                        // Fallback to old structure if new fields are empty
                        $freeStars = $rewardInfo['starslvl'] ?? 0;
                        $freeExp = $rewardInfo['explvl'] ?? 0;
                        $expNeeded = $rewardInfo['explvl'] ?? ($level * 100);
                        
                        if ($freeStars > 0 || $freeExp > 0) {
                            $paidStars = $freeStars + 1; // Premium gets 1 extra star
                            $paidExp = ($freeExp > 0) ? ($freeExp + 25) : 0; // Premium gets +25 extra exp
                            $freeMoney = 0; // Old structure doesn't have money rewards
                            $paidMoney = 0;
                            $useTestData = false;
                        }
                    }
                }
                
                // Use test data if no meaningful database data found
                if ($useTestData) {
                    // Generate test data based on your database structure
                    $expNeeded = $level * 100;
                    
                    // Free rewards pattern (StarReward, ExpReward, ItemCode) - More comprehensive test data
                    if ($level == 1) { $freeStars = 0; $freeExp = 10; $freeMoney = 0; }
                    elseif ($level == 2) { $freeStars = 1; $freeExp = 0; $freeMoney = 0; }
                    elseif ($level == 3) { $freeStars = 0; $freeExp = 15; $freeMoney = 500; }
                    elseif ($level == 4) { $freeStars = 0; $freeExp = 25; $freeMoney = 0; }
                    elseif ($level == 5) { $freeStars = 2; $freeExp = 0; $freeMoney = 750; }
                    elseif ($level == 6) { $freeStars = 1; $freeExp = 30; $freeMoney = 0; }
                    elseif ($level == 7) { $freeStars = 0; $freeExp = 50; $freeMoney = 0; }
                    elseif ($level == 8) { $freeStars = 0; $freeExp = 0; $freeMoney = 1000; }
                    elseif ($level == 9) { $freeStars = 1; $freeExp = 0; $freeMoney = 0; }
                    elseif ($level == 10) { $freeStars = 2; $freeExp = 40; $freeMoney = 1200; }
                    elseif ($level == 11) { $freeStars = 0; $freeExp = 75; $freeMoney = 0; }
                    elseif ($level == 12) { $freeStars = 1; $freeExp = 0; $freeMoney = 0; }
                    elseif ($level == 13) { $freeStars = 0; $freeExp = 100; $freeMoney = 0; }
                    elseif ($level == 14) { $freeStars = 3; $freeExp = 50; $freeMoney = 1500; }
                    elseif ($level == 15) { $freeStars = 2; $freeExp = 80; $freeMoney = 2000; }
                    elseif ($level == 16) { $freeStars = 0; $freeExp = 0; $freeMoney = 1000; }
                    elseif ($level == 17) { $freeStars = 1; $freeExp = 75; $freeMoney = 0; }
                    elseif ($level == 18) { $freeStars = 2; $freeExp = 0; $freeMoney = 0; }
                    elseif ($level == 19) { $freeStars = 0; $freeExp = 90; $freeMoney = 1800; }
                    elseif ($level == 20) { $freeStars = 4; $freeExp = 100; $freeMoney = 2500; }
                    elseif ($level == 21) { $freeStars = 0; $freeExp = 50; $freeMoney = 0; }
                    else { 
                        $freeStars = ($level % 3 == 0) ? 2 : (($level % 2 == 0) ? 1 : 0); 
                        $freeExp = ($level % 4 == 0) ? 50 : (($level % 3 == 0) ? 25 : 0); 
                        $freeMoney = ($level % 5 == 0) ? 1000 : 0; 
                    }
                    
                    // Premium rewards pattern (StarRewardPaid, ExpRewardPaid, ItemCodePaid) - Including all 3 types
                    if ($level == 1) { $paidStars = 2; $paidExp = 25; $paidMoney = 1000; }
                    elseif ($level == 2) { $paidStars = 1; $paidExp = 35; $paidMoney = 0; }
                    elseif ($level == 3) { $paidStars = 3; $paidExp = 0; $paidMoney = 1500; }
                    elseif ($level == 4) { $paidStars = 3; $paidExp = 50; $paidMoney = 2000; }
                    elseif ($level == 5) { $paidStars = 4; $paidExp = 60; $paidMoney = 0; }
                    elseif ($level == 6) { $paidStars = 5; $paidExp = 0; $paidMoney = 2500; }
                    elseif ($level == 7) { $paidStars = 2; $paidExp = 75; $paidMoney = 1800; }
                    elseif ($level == 8) { $paidStars = 6; $paidExp = 100; $paidMoney = 3000; }
                    elseif ($level == 9) { $paidStars = 3; $paidExp = 0; $paidMoney = 0; }
                    elseif ($level == 10) { $paidStars = 4; $paidExp = 120; $paidMoney = 4000; }
                    elseif ($level == 11) { $paidStars = 0; $paidExp = 100; $paidMoney = 2200; }
                    elseif ($level == 12) { $paidStars = 2; $paidExp = 80; $paidMoney = 3500; }
                    elseif ($level == 13) { $paidStars = 5; $paidExp = 175; $paidMoney = 0; }
                    elseif ($level == 14) { $paidStars = 7; $paidExp = 150; $paidMoney = 5000; }
                    elseif ($level == 15) { $paidStars = 3; $paidExp = 200; $paidMoney = 6000; }
                    elseif ($level == 16) { $paidStars = 4; $paidExp = 0; $paidMoney = 3000; }
                    elseif ($level == 17) { $paidStars = 2; $paidExp = 150; $paidMoney = 0; }
                    elseif ($level == 18) { $paidStars = 4; $paidExp = 180; $paidMoney = 4500; }
                    elseif ($level == 19) { $paidStars = 6; $paidExp = 0; $paidMoney = 7000; }
                    elseif ($level == 20) { $paidStars = 8; $paidExp = 250; $paidMoney = 10000; }
                    elseif ($level == 21) { $paidStars = 3; $paidExp = 200; $paidMoney = 5500; }
                    else { 
                        $paidStars = ($level % 3 == 0) ? 4 : (($level % 2 == 0) ? 3 : 2); 
                        $paidExp = ($level % 4 == 0) ? 150 : (($level % 3 == 0) ? 100 : 50); 
                        $paidMoney = ($level % 5 == 0) ? 5000 : (($level % 4 == 0) ? 2000 : 0);
                    }
                }
                
                // Determine level state
                $stateClass = '';
                $isTaken = in_array($level, $takenLevels);
                
                // Check if user has premium pass
                $hasPremiumPass = !empty($user) && isset($user[0]['paid']) && $user[0]['paid'] == 1;
                
                if ($isTaken) {
                    $stateClass = 'earned';
                } elseif ($level == $currentLevel) {
                    $stateClass = 'current';
                } elseif ($level < $currentLevel) {
                    $stateClass = 'earned';
                }
                ?>
                <div class="reward-level-item <?php echo $stateClass; ?>" data-level="<?php echo $level; ?>" data-exp-needed="<?php echo $expNeeded; ?>">
                    <div class="level-number"><?php echo $level; ?></div>
                    <div class="level-label">ლეველი</div>
                    <div class="level-exp-needed"><?php echo number_format($expNeeded); ?> EXP</div>
                    
                    <div class="reward-content">
                        <!-- Free Rewards -->
                        <div class="reward-tier free-tier">
                            <div class="tier-label">🆓</div>
                            <div class="tier-rewards">
                                <?php if ($freeStars > 0): ?>
                                    <div class="reward-item stars">
                                        <span class="reward-value"><?php echo $freeStars; ?></span>
                                        <span class="reward-icon">★</span>
                                    </div>
                                <?php endif; ?>
                                <?php if ($freeExp > 0): ?>
                                    <div class="reward-item exp">
                                        <span class="reward-value">+<?php echo $freeExp; ?></span>
                                        <span class="reward-label">EXP</span>
                                    </div>
                                <?php endif; ?>
                                <?php if ($freeMoney > 0): ?>
                                    <div class="reward-item money">
                                        <span class="reward-value"><?php echo number_format($freeMoney); ?></span>
                                        <span class="reward-label">₾</span>
                                    </div>
                                <?php endif; ?>
                                <?php if ($freeStars == 0 && $freeExp == 0 && $freeMoney == 0): ?>
                                    <div class="reward-item empty">
                                        <span class="reward-value">-</span>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <!-- Premium Rewards -->
                        <div class="reward-tier premium-tier <?php echo !$hasPremiumPass ? 'locked' : ''; ?>">
                            <div class="tier-label">👑</div>
                            <div class="tier-rewards">
                                <?php if ($paidStars > 0): ?>
                                    <div class="reward-item stars">
                                        <span class="reward-value"><?php echo $paidStars; ?></span>
                                        <span class="reward-icon">★</span>
                                    </div>
                                <?php endif; ?>
                                <?php if ($paidExp > 0): ?>
                                    <div class="reward-item exp">
                                        <span class="reward-value">+<?php echo $paidExp; ?></span>
                                        <span class="reward-label">EXP</span>
                                    </div>
                                <?php endif; ?>
                                <?php if ($paidMoney > 0): ?>
                                    <div class="reward-item money">
                                        <span class="reward-value"><?php echo number_format($paidMoney); ?></span>
                                        <span class="reward-label">₾</span>
                                    </div>
                                <?php endif; ?>
                                <?php if ($paidStars == 0 && $paidExp == 0 && $paidMoney == 0): ?>
                                    <div class="reward-item empty">
                                        <span class="reward-value">-</span>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php
            }
            ?>
        </div>
        <?php
        return ob_get_clean();
    }
}