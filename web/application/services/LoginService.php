<?php

namespace Services;

use http\Client\Curl\User;
use Model\User_model;

require_once(dirname(__FILE__). '/../services/ValidateService.php');

class LoginService extends ValidateService
{
    public static function getSessionLoginHash(User_model $user, $time)
    {
        $salt = 'frozeneon_back_test_session_login';

        return md5(md5($user->get_password_hash() . $time . $salt));
    }

    public static function auth(User_model $user)
    {
        $time = time();
        $user->set_time_last_login($time);
        $user->set_session_login_hash(self::getSessionLoginHash($user, $time));

        return $user;
    }

    /**
     * @param array $post
     * @return array
     */
    public static function login(array $post): array
    {
        return self::validatePostParams($post, 'LoginServiceHelper', 'LoginServiceHelper', 'login');
    }
}