<?php


use Library\My_Core;
use Model\User_model;
use System\Libraries\Core;

class Money extends MY_Controller
{

    public function __construct()
    {
        parent::__construct();

        if (is_prod()) {
            die('In production it will be hard to debug! Run as development environment!');
        }
    }

    public function add()
    {
        // task 4, пополнение баланса
        if (!User_model::is_logged()) {
            return $this->response_error(Core::RESPONSE_GENERIC_NEED_AUTH);
        }
        $sum = (float)App::get_ci()->input->post('sum');

        try {
            $user = User_model::get_user();
            $user->is_loaded(TRUE);

            $result = $user->add_money($sum);

            if ($result) {
                $user->reload();
            }
        } catch (EmeraldModelLoadException $e) {
            return $this->response_error(My_Core::RESPONSE_GENERIC_WRONG_PARAMS);
        }

        return $result
            ? $this->response_success(['wallet_balance' => $user->get_wallet_balance()])
            : $this->response_error();
    }
}
