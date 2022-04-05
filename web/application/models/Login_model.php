<?php

namespace Model;

use App;
use Exception;
use http\Client\Curl\User;
use Services\LoginService;
use System\Core\CI_Model;

require_once(dirname(__FILE__). '/../services/LoginService.php');

class Login_model extends CI_Model {

    public function __construct()
    {
        parent::__construct();

    }

    public static function logout()
    {
        App::get_ci()->session->unset_userdata('id');
        App::get_ci()->session->unset_tempdata('session_login_hash');
    }

    /**
     * @param User_model $user
     * @return void
     * @throws Exception
     */
    public static function login(User_model $user)
    {
        $user = LoginService::auth($user);
        self::start_session($user);
    }

    public static function start_session(User_model $user)
    {
        // если перенедан пользователь
        if (empty($user))
        {
            throw new Exception('No user provided!');
        }

        #App::get_ci()->session->set_userdata('session_login_hash', $user->get_session_login_hash());
        App::get_ci()->session->set_userdata(['id' => $user->get_id()]);
        App::get_ci()->session->set_tempdata('session_login_hash', $user->get_session_login_hash());
    }
}
