<?php

class UserHelper {
    public static function getNicknames($steamids) {
        if (empty($steamids)) {
            return [];
        }
        
        $nicknames = [];
        foreach ($steamids as $steamid) {
            $filePath = $_SERVER['DOCUMENT_ROOT'] . "/storage/cache/img/avatars/" . $steamid . ".json";
            if (file_exists($filePath)) {
                $avatarData = json_decode(file_get_contents($filePath), true);
                $nicknames[$steamid] = $avatarData['name'] ?? 'Unknown';
            } else {
                $nicknames[$steamid] = 'Unknown';
            }
        }
        
        return $nicknames;
    }
    
    public static function getAvatarData($steamid, $domain) {
        $filePath = $_SERVER['DOCUMENT_ROOT'] . "/storage/cache/img/avatars/" . $steamid . ".json";
        $avatarData = file_exists($filePath) ? json_decode(file_get_contents($filePath), true) : null;
        
        return [
            'url' => $avatarData['avatar'] ?? $domain . '/app/modules/module_page_battlepass/assets/img/NoImage.webp',
            'name' => $avatarData['name'] ?? 'Unknown'
        ];
    }
}