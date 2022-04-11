<?php

use Library\My_Core;
use Model\Login_model;
use Model\User_model;
use System\Emerald\Exception\EmeraldModelLoadException;
use System\Libraries\Core;

class Auth extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();

        if (is_prod()) {
            die('In production it will be hard to debug! Run as development environment!');
        }
    }


    public function login()
    {
        // task 1, аутентификация
        if (User_model::is_logged()) {
            return $this->response_error(My_Core::RESPONSE_GENERIC_ALLREADY_LOGGED);
        }

        $login = (string)App::get_ci()->input->post('login');
        $password = (string)App::get_ci()->input->post('password');

        try {
            Login_model::login($login, $password);
        } catch (EmeraldModelLoadException $e) {
            return $this->response_error(My_Core::RESPONSE_GENERIC_AUTH_LOGGED);
        }

        return $this->response_success();
    }

    public function logout()
    {
        // task 1, аутентификация
        if (!User_model::is_logged()) {
            return $this->response_error(Core::RESPONSE_GENERIC_NEED_AUTH);
        }

        Login_model::logout();

        return $this->response_success();
    }
}
