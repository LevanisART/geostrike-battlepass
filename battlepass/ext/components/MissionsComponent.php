<?php

class MissionsComponent {
    public static function render($missionData, $usid, $Translate) {
        ob_start();
        ?>
        <div class="missions-list">
            <?php if (!empty($missionData)): ?>
                <?php foreach (array_slice($missionData, 0, 3) as $index => $mission): ?>
                    <div class="mission-card">
                        <div class="mission-icon">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                                <?php 
                                // Different icons based on mission type or use a default
                                $iconPath = "M12 2C13.1 2 14 2.9 14 4C14 5.1 13.1 6 12 6C10.9 6 10 5.1 10 4C10 2.9 10.9 2 12 2M21 9V7L15 1H5C3.9 1 3 1.9 3 3V21C3 22.1 3.9 23 5 23H19C20.1 23 21 22.1 21 21V9M12 8C13.1 8 14 8.9 14 10C14 11.1 13.1 12 12 12C10.9 12 10 11.1 10 10C10 8.9 10.9 8 12 8M12 14C13.1 14 14 14.9 14 16C14 17.1 13.1 18 12 18C10.9 18 10 17.1 10 16C10 14.9 10.9 14 12 14M7 10C8.1 10 9 10.9 9 12C9 13.1 8.1 14 7 14C5.9 14 5 13.1 5 12C5 10.9 5.9 10 7 10M17 10C18.1 10 19 10.9 19 12C19 13.1 18.1 14 17 14C15.9 14 15 13.1 15 12C15 10.9 15.9 10 17 10";
                                
                                if (strpos(strtolower($mission['description'] ?? ''), 'plant') !== false) {
                                    $iconPath = "M11.5 1L10 3H4C2.9 3 2 3.9 2 5V19C2 20.1 2.9 21 4 21H18C19.1 21 20 20.1 20 19V13.5L22 11.5V8.5L20 6.5V5C20 3.9 19.1 3 18 3H14L12.5 1H11.5M8 19C6.3 19 5 17.7 5 16S6.3 13 8 13 11 14.3 11 16 9.7 19 8 19Z";
                                } elseif (strpos(strtolower($mission['description'] ?? ''), 'wallbang') !== false) {
                                    $iconPath = "M12 9C10.3 9 9 10.3 9 12S10.3 15 12 15 15 13.7 15 12 13.7 9 12 9M12 4.5C17 4.5 21.3 7.6 23 12C21.3 16.4 17 19.5 12 19.5S2.7 16.4 1 12C2.7 7.6 7 4.5 12 4.5M12 7C9.8 7 8 8.8 8 11S9.8 15 12 15 16 13.2 16 11 14.2 7 12 7Z";
                                }
                                ?>
                                <path d="<?php echo $iconPath; ?>"/>
                            </svg>
                        </div>
                        <div class="mission-info">
                            <h4><?php echo htmlspecialchars($mission['description'] ?? 'Mission ' . ($index + 1)); ?></h4>
                            <div class="mission-progress">
                                <div class="progress-bar">
                                    <?php 
                                    $progress = $mission['progress'] ?? 0;
                                    $target = $mission['target'] ?? $mission['nedeed'] ?? 1;
                                    $percentage = $target > 0 ? ($progress / $target) * 100 : 0;
                                    ?>
                                    <div class="progress-fill" style="width: <?php echo $percentage; ?>%"></div>
                                </div>
                                <span class="progress-text"><?php echo $progress; ?>/<?php echo $target; ?></span>
                            </div>
                            <div class="mission-reward">
                                <span class="exp-reward">+<?php echo $mission['exp'] ?? 0; ?> EXP</span>
                                <span class="time-left"><?php echo $mission['time_left'] ?? '00:00:00'; ?></span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <!-- Fallback with example missions if no data -->
                <div class="mission-card">
                    <div class="mission-icon">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12 2C13.1 2 14 2.9 14 4C14 5.1 13.1 6 12 6C10.9 6 10 5.1 10 4C10 2.9 10.9 2 12 2M21 9V7L15 1H5C3.9 1 3 1.9 3 3V21C3 22.1 3.9 23 5 23H19C20.1 23 21 22.1 21 21V9M12 8C13.1 8 14 8.9 14 10C14 11.1 13.1 12 12 12C10.9 12 10 11.1 10 10C10 8.9 10.9 8 12 8M12 14C13.1 14 14 14.9 14 16C14 17.1 13.1 18 12 18C10.9 18 10 17.1 10 16C10 14.9 10.9 14 12 14M7 10C8.1 10 9 10.9 9 12C9 13.1 8.1 14 7 14C5.9 14 5 13.1 5 12C5 10.9 5.9 10 7 10M17 10C18.1 10 19 10.9 19 12C19 13.1 18.1 14 17 14C15.9 14 15 13.1 15 12C15 10.9 15.9 10 17 10"/>
                        </svg>
                    </div>
                    <div class="mission-info">
                        <h4>No missions available</h4>
                        <div class="mission-progress">
                            <div class="progress-bar">
                                <div class="progress-fill" style="width: 0%"></div>
                            </div>
                            <span class="progress-text">0/1</span>
                        </div>
                        <div class="mission-reward">
                            <span class="exp-reward">+0 EXP</span>
                            <span class="time-left">00:00:00</span>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        <?php
        return ob_get_clean();
    }
}