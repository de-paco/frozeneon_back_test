<?php

namespace Model;

use App;
use Exception;
use System\Emerald\Emerald_model;

abstract class Likeable_model extends Emerald_model
{

    /**
     * @param User_model $user
     *
     * @return bool
     * @throws Exception
     */
    public function increment_likes(User_model $user): bool
    {
        App::get_s()->set_transaction_repeatable_read();
        App::get_s()->start_trans()->execute();

        $user->decrement_likes();

        if ( ! $user->decrement_likes())
        {
            App::get_s()->rollback()->execute();
            return FALSE;
        }

        App::get_s()->from(self::get_table())
            ->where(['id' => $this->get_id()])
            ->update(sprintf('likes = likes + %s', App::get_s()->quote(1)))
            ->execute();

        if ( ! App::get_s()->is_affected())
        {
            App::get_s()->rollback()->execute();
            return FALSE;
        }

        App::get_s()->commit()->execute();

        return TRUE;
    }

}