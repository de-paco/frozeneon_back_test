<?php

namespace Model;

use App;
use Exception;
use System\Core\CI_Model;

class Login_model extends CI_Model
{
    public static function logout()
    {
        App::get_ci()->session->unset_userdata('id');
    }

    /**
     * @param User_model $user
     * @throws Exception
     */
    public static function login(User_model $user)
    {
        App::get_ci()->session->set_userdata('id', $user->get_id());
    }
}
