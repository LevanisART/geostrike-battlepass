<?php
echo $Controller->renderPage([
    'nameseason' => $nameseason,
    'daysLeft' => $daysLeft,
    'user' => $user,
    'currentLevel' => $currentLevel, 
    'explvl' => $explvl,
    'top_players' => $top_players,
    'nicknames' => $nicknames,
    'usid' => $usid,
    'missionData' => $missionData,
    'rewardData' => $rewardData,
    'takenLevels' => $takenLevels
]);