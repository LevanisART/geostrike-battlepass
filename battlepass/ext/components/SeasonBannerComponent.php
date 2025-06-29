<?php

class SeasonBannerComponent {
    public static function render($nameseason, $currentLevel, $explvl, $daysLeft, $Translate) {
        ob_start();
        ?>
        <div class="battlepass-header">
            <div class="battlepass-title-section">
                <h1 class="battlepass-title">Battle Pass</h1>
                <p class="battlepass-subtitle">სეზონი 1 ხელმისაწვდომია <?php echo $daysLeft; ?> დღეს</p>
            </div>
            
            <div class="battlepass-level-section">
                <div class="battlepass-level-display">
                    <div class="level-icon">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                        </svg>
                    </div>
                    
                    <div class="level-content">
                        <div class="level-number"><?php echo $currentLevel; ?></div>
                        
                        <div class="level-separator"></div>
                        
                        <div class="level-info">
                            <p class="level-label">ლეველი</p>
                            <p class="xp-progress"><?php echo htmlspecialchars($explvl); ?> EXP</p>
                        </div>
                    </div>
                </div>
                
                <div class="battlepass-icons">
                    <div class="battlepass-stars-display" data-tippy-content="ჯამური ვარსკვლავები">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                        </svg>
                        <span class="star-count">17</span>
                    </div>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
    
    public static function close() {
        return '';
    }
}