<?php

use Library\exceptions\OpenBoosterpackException;
use Library\My_Core;
use Model\Boosterpack_info_model;
use Model\Boosterpack_model;
use Model\Comment_model;
use Model\Item_model;
use Model\User_model;
use System\Emerald\Exception\EmeraldModelLoadException;
use System\Libraries\Core;

class Boosterpack extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();

        if (is_prod()) {
            die('In production it will be hard to debug! Run as development environment!');
        }
    }

    public function get_boosterpacks()
    {
        $boosterpacks = Boosterpack_model::preparation_many(Boosterpack_model::get_all(), 'default');
        return $this->response_success(['boosterpacks' => $boosterpacks]);
    }

    public function buy_boosterpack()
    {
        // task 5, покупка и открытие бустерпака
        // Check user is authorize
        if (!User_model::is_logged()) {
            return $this->response_error(Core::RESPONSE_GENERIC_NEED_AUTH);
        }

        try {
            $id = (int)App::get_ci()->input->post('id');
            $boosterpack = Boosterpack_model::find_by_id($id);
            $boosterpack->is_loaded(TRUE);

            App::get_s()->start_trans()->execute();

            $user = User_model::find_by_id_for_update(User_model::get_session_id());
            $user->is_loaded(TRUE);

            if ($boosterpack->get_price() > $user->get_wallet_balance()) {
                App::get_s()->rollback()->execute();
                return $this->response_error(My_Core::RESPONSE_GENERIC_NO_MONEY);
            }

            $user->remove_money($boosterpack);

            $item = $boosterpack->open();

            $result = $user->add_likes($boosterpack->get_id(), $item->get_price());

            if ($result) {
                App::get_s()->commit()->execute();
                $user->reload();
            } else {
                App::get_s()->rollback()->execute();
            }

        } catch (EmeraldModelLoadException|OpenBoosterpackException $e) {
            return $this->response_error(My_Core::RESPONSE_GENERIC_WRONG_PARAMS);
        }

        if(!$result){
            return $this->response_error();
        }

        return $this->response_success([
            'user' => User_model::preparation($user, 'balance'),
            'boosterpack_item' => Item_model::preparation($item),
            'boosterpack' => Boosterpack_model::preparation($boosterpack),
        ]);
    }

    public function get_boosterpack_info(int $bootserpack_id)
    {
        // проверка авторизации убрана тк чтобы посмотреть состав бустер пака она не нужна

        // получить содержимое бустерпака
        try {
            $boosterpack = Boosterpack_model::find_by_id($bootserpack_id);
            $boosterpack->is_loaded(TRUE);

            $boosterpack_items = Item_model::get_by_boosterpack_id($bootserpack_id);
        } catch (EmeraldModelLoadException $e) {
            return $this->response_error(My_Core::RESPONSE_GENERIC_WRONG_PARAMS);
        }

        return $this->response_success([
            'boosterpack' => Boosterpack_model::preparation($boosterpack),
            'boosterpack_items' => Item_model::preparation_many($boosterpack_items),
        ]);
    }
}
