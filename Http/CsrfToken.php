<?php 

namespace Http;

class CsrfToken {
    public static function generate() {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    public static function check($token)
    {
        return hash_equals($_SESSION['csrf_token'], $token);
    }
}