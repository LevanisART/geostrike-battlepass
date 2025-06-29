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
            <div class="battlepass-main-grid">
                <!-- Left Side: Missions -->
                <div class="battlepass-missions-section">
                    <div class="battlepass-section-header">
                        <h3>ხელმისაწვდომი მისიები</h3>
                        <?php if (!empty($_SESSION['steamid64'])): ?>
                            <div class="battlepass-user-id">UserID: <?php echo $data['usid'] ?: 'N/A'; ?></div>
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
                <?php endif; ?>
            </div>
        </div>
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
                        const isEarned = item.classList.contains('earned');
                        const isCurrent = item.classList.contains('current');
                        
                        // Update tooltip content
                        tooltip.querySelector('.reward-title').textContent = `Level ${level} Reward`;
                        tooltip.querySelector('.reward-description').textContent = `This is a reward for reaching level ${level}.`;
                        
                        // Update claim button
                        if (isEarned) {
                            claimBtn.textContent = 'მიღებული';
                            claimBtn.disabled = true;
                        } else if (isCurrent) {
                            claimBtn.textContent = 'ჯილდოს აღება';
                            claimBtn.disabled = false;
                        } else {
                            claimBtn.textContent = 'არ არის ხელმისაწვდომი';
                            claimBtn.disabled = true;
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
        });
        </script>
        <?php
        return ob_get_clean();
    }
}