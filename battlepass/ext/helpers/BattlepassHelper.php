<?php

class BattlepassHelper {
    public static function processRewardsData($rewards) {
        $rewardData = [];
        if (!empty($rewards)) {
            foreach ($rewards as $reward) {
                $lvl = $reward['id'];
                
                $rewardData[$lvl] = [
                    'explvl' => $reward['exp'],
                    'starslvl' => $reward['stars'],
                    'paylvl' => [
                        $lvl => $reward['paid_pass']
                    ]
                ];
            }
        }
        
        return $rewardData;
    }
    
    public static function processMissionsData($missions, $tasks) {
        $missionData = [];
        if (!empty($missions)) {
            foreach ($missions as $mission) {
                $currentTime = time();
                $expiresTime = $mission['expires'];
                
                if ($expiresTime <= $currentTime) {
                    continue;
                }
                
                $missionData[] = [
                    'name' => $mission['mission'],
                    'progress' => $mission['progress'] ?? 0,
                    'nedeed' => $mission['nedeed'] ?? 0,
                    'expires' => $mission['expires'],
                ];
            }
        }
        
        if (count($missionData) > 10) {
            $missionData = array_slice($missionData, 0, 10);
        }
        
        if (!empty($missionData) && !empty($tasks)) {
            $taskData = [];
            foreach ($tasks as $task) {
                $taskData[$task['identifier']] = [
                    'description' => $task['description'],
                    'exp' => $task['exp'],
                    'stars' => $task['stars'],
                    'paid_pass' => $task['paid_pass'],
                ];
            }
            
            foreach ($missionData as &$mission) {
                $missionName = $mission['name'];
                if (isset($taskData[$missionName])) {
                    $mission['description'] = $taskData[$missionName]['description'];
                    $mission['exp'] = $taskData[$missionName]['exp'];
                    $mission['stars'] = $taskData[$missionName]['stars'];
                    $mission['paid_pass'] = $taskData[$missionName]['paid_pass'];
                }
            }
            unset($mission);
        }
        
        return $missionData;
    }
    
    public static function processSeasonData($seasons) {
        $endDate = 0;
        $nameseason = '';
        foreach ($seasons as $season) {
            if (isset($season['end_date']) && $season['end_date'] > $endDate) {
                $endDate = (int) $season['end_date'];
                $nameseason = $season['name'];
            }
        }
        
        if ($endDate > 0) {
            $currentDate = time();
            $daysLeft = max(0, floor(($endDate - $currentDate) / (60 * 60 * 24)));
        } else {
            $daysLeft = 0;
            $nameseason = 'სეზონი არ მოიძებნა';
        }
        
        return [
            'name' => $nameseason,
            'end_date' => $endDate,
            'days_left' => $daysLeft
        ];
    }
    
    public static function getLevelAvailability($level, $currentLevel, $paylvl, $user) {
        if ($level > $currentLevel) {
            return [
                'available' => false,
                'message' => 'ლეველი მიუწვდომელია'
            ];
        } elseif ($paylvl == 1 && !empty($user) && $user[0]['paid'] == 1) {
            return [
                'available' => true,
                'message' => ''
            ];
        } elseif ($paylvl == 1 && (!empty($user) && $user[0]['paid'] == 0)) {
            return [
                'available' => false,
                'message' => 'საჭიროა Season+'
            ];
        } elseif ($paylvl == 0) {
            return [
                'available' => true,
                'message' => ''
            ];
        } else {
            return [
                'available' => false,
                'message' => 'შეცდომა'
            ];
        }
    }
    
    public static function getLevelClass($level, $takenLevels, $isAvailable) {
        if (in_array($level, $takenLevels)) {
            return 'battlePassInfoTaked';
        } elseif ($isAvailable) {
            return 'battlePassInfoAllow';
        } else {
            return 'battlePassInfo';
        }
    }
}