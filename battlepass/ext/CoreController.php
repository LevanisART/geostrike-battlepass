<?php

require_once __DIR__ . '/models/BattlepassModel.php';
require_once __DIR__ . '/helpers/UserHelper.php';
require_once __DIR__ . '/helpers/BattlepassHelper.php';
require_once __DIR__ . '/helpers/UrlHelper.php';
require_once __DIR__ . '/components/UserInfoComponent.php';
require_once __DIR__ . '/components/TopPlayersComponent.php';
require_once __DIR__ . '/components/MissionsComponent.php';
require_once __DIR__ . '/components/LevelsComponent.php';
require_once __DIR__ . '/components/SeasonBannerComponent.php';
require_once __DIR__ . '/components/AuthMessageComponent.php';

class CoreController {
    private $Db;
    private $General;
    private $Translate;
    private $Modules;
    private $server_group = 0;
    private $res_data = [];
    private $model;

    public function __construct($Db, $General, $Translate, $Modules, $server_group = 0) {
        $this->Db = $Db;
        $this->General = $General;
        $this->Translate = $Translate;
        $this->Modules = $Modules;
        $this->server_group = $server_group;
        
        $this->initialize();
    }
    
    private function initialize() {
        // Skip initialization for testing environment
        if (method_exists($this->General, 'get_default_url_section')) {
            try {
                $this->General->get_default_url_section('filter', 'value', array('value', 'level', 'exp', 'stars', 'season_id', 'paid', 'server_id'));
            } catch (Exception $e) {
                // Ignore errors in test environment
            }
        }
        
        // Only get server_group from GET if not already set in constructor
        if ($this->server_group === 0 && isset($_GET['server_group'])) {
            $this->server_group = (int) $_GET['server_group'];
        }
        
        // Skip database checks in test environment
        if (isset($this->Db->table_statistics_count) && $this->server_group >= $this->Db->table_statistics_count) {
            echo 'სერვერი არ არსებობს'; 
            $this->server_group = 0;
        }
        
        // Skip database operations in test environment
        if (isset($this->Db->table_count['Core'])) {
            for ($d = 0; $d < $this->Db->table_count['Core']; $d++) {
                $this->res_data[] = [
                    'statistics' => 'Core',
                    'name_servers' => $this->Db->db_data['Core'][$d]['name'],
                    'mod' => $this->Db->db_data['Core'][$d]['mod'],
                    'USER_ID' => $this->Db->db_data['Core'][$d]['USER_ID'],
                    'data_db' => $this->Db->db_data['Core'][$d]['DB_num'],
                    'data_servers' => $this->Db->db_data['Core'][$d]['Table']
                ];
            }
            
            $this->model = new BattlepassModel($this->Db, $this->server_group, $this->res_data);
        }
    }
    
    public function getPageData($page_num, $players_per_page) {
        $users = $this->model->getUsersData($page_num, $players_per_page, $_SESSION['filter']);
        $top_players = $this->model->getTopPlayers();
        $steamids = array_column($top_players, 'steamid');
        $nicknames = UserHelper::getNicknames($steamids);
        $user = $this->model->getUserData($_SESSION['steamid64'] ?? '');
        $usid = $user[0]['id'] ?? null;
        $rewards = $this->model->getRewards();
        $rewardData = BattlepassHelper::processRewardsData($rewards);
        $currentLevel = !empty($user) ? $user[0]['level'] : 0;
        $nextLevel = $currentLevel + 1;
        
        if (isset($rewardData[$nextLevel])) {
            $explvl = $rewardData[$nextLevel]['explvl'];
        } else {
            $explvl = 0;
        }
        
        $takenLevelsData = $this->model->getTakenLevels($usid);
        $takenLevels = array_column($takenLevelsData, 'level_id');
        $missions = $this->model->getMissions($usid);
        $missionIdentifiers = array_unique(array_column($missions, 'mission'));
        $tasks = $this->model->getTasks($missionIdentifiers);
        $missionData = BattlepassHelper::processMissionsData($missions, $tasks);
        $seasons = $this->model->getSeasons();
        $seasonData = BattlepassHelper::processSeasonData($seasons);
        $this->setPageTitle();
        
        return [
            'users' => $users,
            'top_players' => $top_players,
            'nicknames' => $nicknames,
            'user' => $user,
            'usid' => $usid,
            'rewardData' => $rewardData,
            'currentLevel' => $currentLevel,
            'explvl' => $explvl,
            'takenLevels' => $takenLevels,
            'missionData' => $missionData,
            'nameseason' => $seasonData['name'],
            'daysLeft' => $seasonData['days_left']
        ];
    }
    
    public function renderPage($data) {
        ob_start();
        ?>
        <div class="battlepass-container">
            <!-- Header Section -->
            <?php echo SeasonBannerComponent::render($data['nameseason'], $data['currentLevel'], $data['explvl'], $data['daysLeft'], $this->Translate); ?>
            
            <!-- Main Content Grid -->
            <div class="battlepass-content-grid">
                <!-- Left Side: Missions -->
                <div class="battlepass-missions-section">
                    <div class="section-header">
                        <h3>ხელმისაწვდომი მისიები</h3>
                        <?php if (!empty($data['usid'])): ?>
                            <span class="user-id">UserID: <?php echo htmlspecialchars($data['usid']); ?></span>
                        <?php endif; ?>
                    </div>
                    
                    <?php if (empty($_SESSION['steamid64'])): ?>
                        <?php echo AuthMessageComponent::render($this->Translate); ?>
                    <?php else: ?>
                        <?php echo MissionsComponent::render($data['missionData'], $data['usid'], $this->Translate); ?>
                    <?php endif; ?>
                </div>
                
                <!-- Right Side: Leaderboard -->
                <div class="battlepass-leaderboard-section">
                    <div class="section-header">
                        <h3>TOP 10 მოთამაშე</h3>
                    </div>
                    <?php echo TopPlayersComponent::render($data['top_players'], $data['nicknames'], $this->Translate); ?>
                </div>
            </div>
            
            <!-- Bottom: Rewards Track -->
            <div class="battlepass-rewards-section">
                <?php if (!empty($data['rewardData'])): ?>
                    <?php echo LevelsComponent::render(
                        $data['rewardData'], 
                        $data['currentLevel'], 
                        $data['takenLevels'], 
                        $data['user'], 
                        $this->Translate
                    ); ?>
                    
                    <!-- Progress bar at bottom -->
                    <div class="battlepass-progress-track">
                        <div class="progress-line">
                            <div class="progress-line-fill" style="width: <?php echo ($data['currentLevel'] / 15) * 100; ?>%;"></div>
                        </div>
                    </div>
                <?php endif; ?>
                
                <!-- Battle Pass Explanation -->
                <div class="battlepass-explanation">
                    <div class="explanation-column">
                        <div class="explanation-header">
                            <div class="explanation-icon free-pass-icon">🆓</div>
                            <div>
                                <h4 class="explanation-title">უფასო Battle Pass</h4>
                                <p class="explanation-subtitle">ყველა მოთამაშისთვის ხელმისაწვდომი</p>
                            </div>
                        </div>
                        <ul class="explanation-features">
                            <li>უფასო ჯილდოები თითოეულ ლიცენტუკურ დონეზე</li>
                            <li>ძირითადი მისიები და გამოცდები</li>
                            <li>სტანდარტული XP ბონუსები</li>
                            <li>TOP 10 ლიდერბორდში მონაწილეობა</li>
                            <li>ძირითადი სკინები და სახელები</li>
                        </ul>
                    </div>
                    
                    <div class="explanation-column">
                        <div class="explanation-header">
                            <div class="explanation-icon premium-pass-icon">👑</div>
                            <div>
                                <h4 class="explanation-title">Premium Battle Pass</h4>
                                <p class="explanation-subtitle">განსაკუთრებული ჯილდოებისთვის</p>
                            </div>
                        </div>
                        <ul class="explanation-features premium-features">
                            <li>ყველა უფასო ჯილდო + Premium ჯილდოები</li>
                            <li>განსაკუთრებული სკინები და იარაღები</li>
                            <li>+50% XP ბონუსი ყველა მისიაზე</li>
                            <li>ექსკლუზივური სახელები და ნიშანები</li>
                            <li>უპრიორიტეტო სერვერზე წვდომა</li>
                            <li>მომავალი სეზონზე ადრეული წვდომა</li>
                        </ul>
                        <?php 
                        $hasPremium = !empty($data['user']) && isset($data['user'][0]['paid']) && $data['user'][0]['paid'] == 1;
                        if (!$hasPremium): 
                        ?>
                        <button class="upgrade-btn" onclick="upgradeToPremium()">
                            Premium-ზე გადართვა - 15₾
                        </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Reward Preview Tooltip -->
        <div class="reward-preview-tooltip" id="rewardTooltip">
            <div class="tooltip-content">
                <h4 class="reward-title">ჯილდოს სახელი</h4>
                <img class="reward-image" src="https://placehold.co/150x150/00bcd4/ffffff?text=Reward" alt="Reward">
                <p class="reward-description">ეს არის ჯილდოს აღწერა...</p>
                <button class="claim-btn" id="claimBtn">ჯილდოს აღება</button>
            </div>
        </div>

        <!-- Notification System -->
        <div class="notification-container" id="notificationContainer"></div>
        <?php
        return ob_get_clean();
    }
    
    public function setPageTitle() {
        $this->Modules->set_page_title(
            $this->Translate->get_translate_module_phrase('module_page_battlepass', '_PassName') . ' | ' . $this->General->arr_general['short_name']
        );
    }
    
    public function getSiteUrl() {
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'];
        return $protocol . '://' . $host . '/';
    }
    
    /**
     * Simple render method for testing purposes
     * Combines all components with provided mock data
     */
    public function renderBattlePass($nameseason, $currentLevel, $explvl, $daysLeft, $usid, $user, $topPlayers, $nicknames, $missionData, $rewardData, $takenLevels) {
        ob_start();
        ?>
        <div class="battlepass-container">
            <!-- Header Section -->
            <?php echo SeasonBannerComponent::render($nameseason, $currentLevel, $explvl, $daysLeft, $this->Translate); ?>
            
            <!-- Main Content Grid -->
            <div class="battlepass-content-grid">
                <!-- Left Side: Missions -->
                <div class="battlepass-missions-section">
                    <div class="section-header">
                        <h3>ხელმისაწვდომი მისიები</h3>
                        <?php if (!empty($usid)): ?>
                            <span class="user-id">UserID: <?php echo htmlspecialchars($usid); ?></span>
                        <?php endif; ?>
                    </div>
                    
                    <?php if (empty($_SESSION['steamid64'])): ?>
                        <?php echo AuthMessageComponent::render($this->Translate); ?>
                    <?php else: ?>
                        <?php echo MissionsComponent::render($missionData, $usid, $this->Translate); ?>
                    <?php endif; ?>
                </div>
                
                <!-- Right Side: Leaderboard -->
                <div class="battlepass-leaderboard-section">
                    <div class="section-header">
                        <h3>TOP 10 მოთამაშე</h3>
                    </div>
                    <?php echo TopPlayersComponent::render($topPlayers, $nicknames, $this->Translate); ?>
                </div>
            </div>
            
            <!-- Bottom: Rewards Track -->
            <div class="battlepass-rewards-section">
                <?php if (!empty($rewardData)): ?>
                    <?php echo LevelsComponent::render($rewardData, $currentLevel, $takenLevels, $user, $this->Translate); ?>
                    
                    <!-- Progress bar at bottom -->
                    <div class="battlepass-progress-track">
                        <div class="progress-line">
                            <div class="progress-line-fill" style="width: <?php echo ($currentLevel / 15) * 100; ?>%;"></div>
                        </div>
                    </div>
                <?php endif; ?>
                
                <!-- Battle Pass Explanation -->
                <div class="battlepass-explanation">
                    <div class="explanation-column">
                        <div class="explanation-header">
                            <div class="explanation-icon free-pass-icon">🆓</div>
                            <div>
                                <h4 class="explanation-title">უფასო Battle Pass</h4>
                                <p class="explanation-subtitle">ყველა მოთამაშისთვის ხელმისაწვდომი</p>
                            </div>
                        </div>
                        <ul class="explanation-features">
                            <li>უფასო ჯილდოები თითოეულ ლიცენტუკურ დონეზე</li>
                            <li>ძირითადი მისიები და გამოცდები</li>
                            <li>სტანდარტული XP ბონუსები</li>
                            <li>TOP 10 ლიდერბორდში მონაწილეობა</li>
                            <li>ძირითადი სკინები და სახელები</li>
                        </ul>
                    </div>
                    
                    <div class="explanation-column">
                        <div class="explanation-header">
                            <div class="explanation-icon premium-pass-icon">👑</div>
                            <div>
                                <h4 class="explanation-title">Premium Battle Pass</h4>
                                <p class="explanation-subtitle">განსაკუთრებული ჯილდოებისთვის</p>
                            </div>
                        </div>
                        <ul class="explanation-features premium-features">
                            <li>ყველა უფასო ჯილდო + Premium ჯილდოები</li>
                            <li>განსაკუთრებული სკინები და იარაღები</li>
                            <li>+50% XP ბონუსი ყველა მისიაზე</li>
                            <li>ექსკლუზივური სახელები და ნიშანები</li>
                            <li>უპრიორიტეტო სერვერზე წვდომა</li>
                            <li>მომავალი სეზონზე ადრეული წვდომა</li>
                        </ul>
                        <?php 
                        $hasPremium = !empty($user) && isset($user[0]['paid']) && $user[0]['paid'] == 1;
                        if (!$hasPremium): 
                        ?>
                        <button class="upgrade-btn" onclick="upgradeToPremium()">
                            Premium-ზე გადართვა - 15₾
                        </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Reward Preview Tooltip -->
        <div class="reward-preview-tooltip" id="rewardTooltip">
            <div class="tooltip-content">
                <h4 class="reward-title">ჯილდოს სახელი</h4>
                <img class="reward-image" src="https://placehold.co/150x150/00bcd4/ffffff?text=Reward" alt="Reward">
                <p class="reward-description">ეს არის ჯილდოს აღწერა...</p>
                <button class="claim-btn" id="claimBtn">ჯილდოს აღება</button>
            </div>
        </div>

        <!-- Notification System -->
        <div class="notification-container" id="notificationContainer"></div>
        </div>
        
        <!-- JavaScript for interactivity -->
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Reward Preview Tooltip
            const rewardItems = document.querySelectorAll('.reward-level-item');
            const tooltip = document.getElementById('rewardTooltip');
            const claimBtn = document.getElementById('claimBtn');
            
            if (tooltip && claimBtn) {
                rewardItems.forEach(item => {
                    item.addEventListener('click', (e) => {
                        const level = item.dataset.level;
                        const expNeeded = item.dataset.expNeeded;
                        const isEarned = item.classList.contains('earned');
                        const isCurrent = item.classList.contains('current');
                        
                        // Get reward information from the HTML structure
                        const freeTier = item.querySelector('.free-tier');
                        const premiumTier = item.querySelector('.premium-tier');
                        const isLocked = premiumTier?.classList.contains('locked') || false;
                        
                        // Update tooltip content
                        tooltip.querySelector('.reward-title').textContent = `Level ${level} Rewards`;
                        
                        let description = `შეჭირა ${expNeeded} EXP ამ ლეველის მისაღწევად.\n\n`;
                        description += 'უფასო ჯილდოები: ';
                        
                        const freeStars = freeTier?.querySelector('.stars .reward-value')?.textContent || '0';
                        const freeExp = freeTier?.querySelector('.exp .reward-value')?.textContent || '0';
                        const freeMoney = freeTier?.querySelector('.money .reward-value')?.textContent || '0';
                        
                        let freeRewards = [];
                        if (freeStars !== '0') freeRewards.push(`${freeStars}★`);
                        if (freeExp !== '0') freeRewards.push(`${freeExp} EXP`);
                        if (freeMoney !== '0') freeRewards.push(`${freeMoney}₾`);
                        description += freeRewards.length > 0 ? freeRewards.join(', ') : '-';
                        
                        description += '\n\nPremium ჯილდოები: ';
                        const premiumStars = premiumTier?.querySelector('.stars .reward-value')?.textContent || '0';
                        const premiumExp = premiumTier?.querySelector('.exp .reward-value')?.textContent || '0';
                        const premiumMoney = premiumTier?.querySelector('.money .reward-value')?.textContent || '0';
                        
                        let premiumRewards = [];
                        if (premiumStars !== '0') premiumRewards.push(`${premiumStars}★`);
                        if (premiumExp !== '0') premiumRewards.push(`${premiumExp} EXP`);
                        if (premiumMoney !== '0') premiumRewards.push(`${premiumMoney}₾`);
                        description += premiumRewards.length > 0 ? premiumRewards.join(', ') : '-';
                        
                        if (isLocked) {
                            description += '\n\n⚠️ Premium Battle Pass საჭიროა premium ჯილდოებისთვის';
                        }
                        
                        tooltip.querySelector('.reward-description').style.whiteSpace = 'pre-line';
                        tooltip.querySelector('.reward-description').textContent = description;
                        
                        // Update claim button
                        if (isEarned) {
                            claimBtn.textContent = 'ყველა ჯილდო მიღებული';
                            claimBtn.disabled = true;
                            claimBtn.style.background = 'var(--span-color)';
                            claimBtn.style.color = 'var(--color-default)';
                        } else if (isCurrent) {
                            claimBtn.textContent = 'ჯილდოების აღება';
                            claimBtn.disabled = false;
                            claimBtn.style.background = 'var(--span-color)';
                            claimBtn.style.color = 'var(--color-default)';
                        } else {
                            claimBtn.textContent = 'არ არის ხელმისაწვდომი';
                            claimBtn.disabled = true;
                            claimBtn.style.background = '#666';
                        }
                        
                        // Position and show tooltip
                        const rect = item.getBoundingClientRect();
                        tooltip.style.left = `${rect.left + window.scrollX}px`;
                        tooltip.style.top = `${rect.bottom + window.scrollY + 10}px`;
                        tooltip.style.display = 'block';
                    });
                });

                // Hide tooltip when clicking outside
                document.addEventListener('click', (e) => {
                    if (!e.target.closest('.reward-level-item') && !e.target.closest('.reward-preview-tooltip')) {
                        tooltip.style.display = 'none';
                    }
                });

                // Claim button functionality
                claimBtn.addEventListener('click', () => {
                    if (!claimBtn.disabled) {
                        showNotification('Reward Claimed!', 'success');
                        claimBtn.textContent = 'მიღებული';
                        claimBtn.disabled = true;
                        tooltip.style.display = 'none';
                    }
                });
            }

            // Notification System
            function showNotification(message, type = 'info') {
                const notification = document.createElement('div');
                notification.className = `notification ${type}`;
                notification.textContent = message;
                
                const container = document.getElementById('notificationContainer');
                if (container) {
                    container.appendChild(notification);
                    
                    // Remove notification after 3 seconds
                    setTimeout(() => {
                        notification.style.animation = 'slideIn 0.3s ease reverse';
                        setTimeout(() => {
                            if (notification.parentNode) {
                                notification.remove();
                            }
                        }, 300);
                    }, 3000);
                }
            }

            // Make showNotification globally available
            window.showNotification = showNotification;
            
            // Premium upgrade function
            window.upgradeToPremium = function() {
                if (confirm('Premium Battle Pass-ზე გადართვა ღირს 15₾. გნებავთ გაგრძელება?')) {
                    // Here you would integrate with your payment system
                    // For now, just show a notification
                    showNotification('Premium Battle Pass-ისთვის გადართვა მუშავდება...', 'info');
                    
                    // You can redirect to payment page or open payment modal
                    // window.location.href = '/payment/battlepass-premium';
                }
            };
        });
        </script>
        <?php
        return ob_get_clean();
    }
}