<?php

class BattlepassModel {
    private $Db;
    private $server_group;
    private $res_data = [];
    
    public function __construct($Db, $server_group, $res_data) {
        $this->Db = $Db;
        $this->server_group = $server_group;
        $this->res_data = $res_data;
    }
    
    public function getUsersData($page_num, $players_per_page, $filter) {
        $page_num = max(1, intval($page_num));
        $players_per_page = max(1, intval($players_per_page));
        $offset = ($page_num - 1) * $players_per_page;
        
        $filterMap = [
            'value' => 'p.SteamId',
            'level' => 'p.LevelId',
            'exp' => 'p.CurrentExp',
            'stars' => 'p.CurrentStars',
            'season_id' => 'pq.Season',
            'paid' => 'p.HasPaidBP',
            'server_id' => 'p.SteamId' // ?????
        ];
        
        $orderByColumn = isset($filterMap[$filter]) ? $filterMap[$filter] : 'p.SteamId';
        
        return $this->Db->queryAll(
            'Core',
            $this->res_data[$this->server_group]['USER_ID'],
            $this->res_data[$this->server_group]['data_db'],
            "SELECT p.SteamId as steamid, p.LevelId as level, p.CurrentExp as exp, 
                   p.CurrentStars as stars, pq.Season as season_id, 
                   p.HasPaidBP as paid, 0 as server_id, p.SteamId as id
             FROM `bp_Player` p
             LEFT JOIN `bp_PlayerQuests` pq ON p.SteamId = pq.PlayerSteamId
             WHERE p.SteamId IN (
                 SELECT DISTINCT PlayerSteamId FROM `bp_PlayerQuests`
             )
             GROUP BY p.SteamId
             ORDER BY {$orderByColumn} DESC 
             LIMIT {$offset}, {$players_per_page}"
        );
    }
    
    public function getTopPlayers() {
        return $this->Db->queryAll(
            'Core',
            $this->res_data[$this->server_group]['USER_ID'],
            $this->res_data[$this->server_group]['data_db'],
            "SELECT p.SteamId as steamid, p.LevelId as level, p.CurrentExp as exp, 
                   p.CurrentStars as stars, pq.Season as season_id, 
                   p.HasPaidBP as paid, 0 as server_id, p.SteamId as id
             FROM `bp_Player` p
             LEFT JOIN `bp_PlayerQuests` pq ON p.SteamId = pq.PlayerSteamId
             WHERE p.SteamId IN (
                 SELECT DISTINCT PlayerSteamId FROM `bp_PlayerQuests`
             )
             GROUP BY p.SteamId
             ORDER BY p.CurrentExp DESC 
             LIMIT 10" // 100 ????
        );
    }
    
    public function getUserData($steamid64) {
        if (empty($steamid64)) {
            return [];
        }
        
        return $this->Db->queryAll(
            'Core',
            $this->res_data[$this->server_group]['USER_ID'],
            $this->res_data[$this->server_group]['data_db'],
            "SELECT p.SteamId as steamid, p.LevelId as level, p.CurrentExp as exp, 
                   p.CurrentStars as stars, pq.Season as season_id, 
                   p.HasPaidBP as paid, 0 as server_id, p.SteamId as id
             FROM `bp_Player` p
             LEFT JOIN `bp_PlayerQuests` pq ON p.SteamId = pq.PlayerSteamId
             WHERE p.SteamId = :steamid
             GROUP BY p.SteamId",
            ["steamid" => $steamid64]
        );
    }
    
    public function getRewards() {
        return $this->Db->queryAll(
            'Core',
            $this->res_data[$this->server_group]['USER_ID'],
            $this->res_data[$this->server_group]['data_db'],
            "SELECT `Id` as id, `ExpNeeded` as exp, `StarReward` as stars, 
                    CASE WHEN `ItemCodePaid` IS NOT NULL THEN `ItemCodePaid` ELSE 0 END as paid_pass
             FROM `bp_Level` 
             ORDER BY `Id` ASC"
        );
    }
    
    public function getTakenLevels($userId) {
        if (empty($userId)) {
            return [];
        }
        
        $playerData = $this->Db->queryAll(
            'Core',
            $this->res_data[$this->server_group]['USER_ID'],
            $this->res_data[$this->server_group]['data_db'],
            "SELECT `ItemIds` FROM `bp_Player` WHERE `SteamId` = :user_id",
            ["user_id" => $userId]
        );
        
        if (empty($playerData) || empty($playerData[0]['ItemIds'])) {
            return [];
        }
        
        $itemIds = explode(',', $playerData[0]['ItemIds']);
        $result = [];
        
        foreach ($itemIds as $itemId) {
            if (!empty($itemId)) {
                $result[] = ['level_id' => (int)$itemId];
            }
        }
        
        return $result;
    }
    
    public function getMissions($userId) {
        if (empty($userId)) {
            return [];
        }
        
        $missions = $this->Db->queryAll(
            'Core',
            $this->res_data[$this->server_group]['USER_ID'],
            $this->res_data[$this->server_group]['data_db'],
            "SELECT 
                pq.QuestId as id, 
                q.Name as mission,
                pq.Progress as progress_json,
                CASE 
                    WHEN q.KillCount > 0 THEN q.KillCount
                    WHEN q.PlantCount > 0 THEN q.PlantCount
                    WHEN q.WinCount > 0 THEN q.WinCount
                    WHEN q.GrenadeKill > 0 THEN q.GrenadeKill
                    ELSE 1
                END as nedeed,
                CASE
                    WHEN q.KillCount > 0 THEN 'KillCount'
                    WHEN q.PlantCount > 0 THEN 'PlantCount'
                    WHEN q.WinCount > 0 THEN 'WinCount'
                    WHEN q.GrenadeKill > 0 THEN 'GrenadeKill'
                    ELSE 'KillCount'
                END as progress_type,
                UNIX_TIMESTAMP(DATE_ADD(NOW(), INTERVAL 1 DAY)) as expires
             FROM `bp_PlayerQuests` pq
             JOIN `bp_Quest` q ON pq.QuestId = q.Id
             WHERE pq.PlayerSteamId = :user_id
             AND pq.Status = 'Active'
             ORDER BY q.Name ASC",
            ["user_id" => $userId]
        );
        

        foreach ($missions as &$mission) {
            $progressJson = json_decode($mission['progress_json'], true);
            $progressType = $mission['progress_type'];
            

            $mission['progress'] = isset($progressJson[$progressType]) ? (int)$progressJson[$progressType] : 0;
            

            unset($mission['progress_json']);
            unset($mission['progress_type']);
        }
        
        return $missions;
    }
    
    public function getTasks($identifiers) {
        if (empty($identifiers)) {
            return [];
        }
        
        $namedParams = [];
        $placeholders = [];
        
        foreach ($identifiers as $index => $identifier) {
            $paramName = "identifier_" . $index;
            $namedParams[$paramName] = $identifier;
            $placeholders[] = ":{$paramName}";
        }
        
        $placeholdersStr = implode(',', $placeholders);
        
        return $this->Db->queryAll(
            'Core',
            $this->res_data[$this->server_group]['USER_ID'],
            $this->res_data[$this->server_group]['data_db'],
            "SELECT `Name` as identifier, `Description` as description, 
                    `ExpReward` as exp, `StarReward` as stars, 
                    `NeedsPaidPB` as paid_pass
             FROM `bp_Quest` 
             WHERE `Name` IN ($placeholdersStr)",
            $namedParams
        );
    }
    
    public function getSeasons() {
        return $this->Db->queryAll(
            'Core',
            $this->res_data[$this->server_group]['USER_ID'],
            $this->res_data[$this->server_group]['data_db'],
            "SELECT DISTINCT 
                q.Season as id, 
                CONCAT('Season ', q.Season) as name,
                UNIX_TIMESTAMP(DATE_ADD(NOW(), INTERVAL 30 DAY)) as end_date,
                UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL 30 DAY)) as start_date,
                0 as server_id
             FROM `bp_Quest` q
             WHERE q.Season IS NOT NULL
             ORDER BY q.Season DESC"
        );
    }
}