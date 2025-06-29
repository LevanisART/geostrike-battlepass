<?php

class UrlHelper {
    public static function getSiteUrl() {
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'];
        return $protocol . '://' . $host . '/';
    }
    
    public static function getCurrentDomain() {
        return $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'];
    }
    
    public static function safeEcho($var) {
        return htmlspecialchars($var ?? '');
    }
}