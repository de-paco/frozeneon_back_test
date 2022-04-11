<?php

namespace Model;

use App;
use Exception;
use Library\exceptions\AuthException;
use System\Core\CI_Model;

class Login_model extends CI_Model {

    public function __construct()
    {
        parent::__construct();
    }

    public static function logout()
    {
        App::get_ci()->session->unset_userdata('id');
    }

    /**
     * @return User_model
     * @throws Exception
     */
    public static function login(string $email, string $password): User_model
    {
        // task 1, аутентификация
        $user = User_model::find_user_by_email($email);
        $user->is_loaded(TRUE);

        if (!password_verify($password, $user->get_password())) {
            throw new AuthException('Wrong email or password');
        }

        self::start_session($user->get_id());

        return $user;
    }

    public static function start_session(int $user_id)
    {
        // если перенедан пользователь
        if (empty($user_id))
        {
            throw new Exception('No id provided!');
        }

        App::get_ci()->session->set_userdata('id', $user_id);
    }
}
