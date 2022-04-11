<?php

use Model\Boosterpack_model;
use Model\User_model;

/**
 * Created by PhpStorm.
 * User: mr.incognito
 * Date: 10.11.2018
 * Time: 21:36
 */
class Main_page extends MY_Controller
{

    public function __construct()
    {

        parent::__construct();

        if (is_prod())
        {
            die('In production it will be hard to debug! Run as development environment!');
        }
    }

    public function index()
    {
        $user = User_model::get_user();

        $preparation = User_model::is_logged() ? 'main_page' : 'default';

        App::get_ci()->load->view('main_page', ['user' => User_model::preparation($user, $preparation)]);
    }
}
