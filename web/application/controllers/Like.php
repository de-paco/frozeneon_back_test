<?php

use Library\My_Core;
use Model\Comment_model;
use Model\Post_model;
use Model\User_model;
use System\Libraries\Core;

class Like extends MY_Controller
{

    public function __construct()
    {
        parent::__construct();

        if (is_prod()) {
            die('In production it will be hard to debug! Run as development environment!');
        }
    }

    public function like_comment(int $comment_id)
    {
        if (!User_model::is_logged()) {
            return $this->response_error(Core::RESPONSE_GENERIC_NEED_AUTH);
        }

        try {
            App::get_s()->start_trans()->execute();

            $user = User_model::find_by_id_for_update(User_model::get_session_id());
            $user->is_loaded(TRUE);

            $comment = Comment_model::find_by_id($comment_id);
            $comment->is_loaded(TRUE);

            $result = $comment->increment_likes($user);

            if ($result) {
                App::get_s()->commit()->execute();
                $user->reload();
            } else {
                App::get_s()->rollback()->execute();
            }

        } catch (EmeraldModelLoadException $e) {
            return $this->response_error(My_Core::RESPONSE_GENERIC_WRONG_PARAMS);
        }

        return $result
            ? $this->response_success(['likes_balance' => $user->get_likes_balance()])
            : $this->response_error();
    }

    public function like_post(int $post_id)
    {
        if (!User_model::is_logged()) {
            return $this->response_error(Core::RESPONSE_GENERIC_NEED_AUTH);
        }

        try {
            App::get_s()->start_trans()->execute();

            $user = User_model::find_by_id_for_update(User_model::get_session_id());
            $user->is_loaded(TRUE);

            $post = Post_model::find_by_id($post_id);
            $post->is_loaded(TRUE);

            $result = $post->increment_likes($user);

            if ($result) {
                App::get_s()->commit()->execute();
                $user->reload();
            } else {
                App::get_s()->rollback()->execute();
            }

        } catch (EmeraldModelLoadException $e) {
            return $this->response_error(My_Core::RESPONSE_GENERIC_WRONG_PARAMS);
        }

        return $result
            ? $this->response_success(['likes_balance' => $user->get_likes_balance()])
            : $this->response_error();
    }
}
