<?php

use Library\My_Core;
use Model\Comment_model;
use Model\Post_model;
use Model\User_model;
use System\Libraries\Core;

class Comment extends MY_Controller
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
        // task 2, комментирование

        if (!User_model::is_logged()) {
            return $this->response_error(Core::RESPONSE_GENERIC_NEED_AUTH);
        }

        $post_id = (int)App::get_ci()->input->post('postId');
        $comment_text = (string)App::get_ci()->input->post('commentText');
        try {
            $post = Post_model::find_by_id($post_id);
            $post->is_loaded(TRUE);

            $user = User_model::get_user();
            $user->is_loaded(TRUE);

            Comment_model::add_comment($user, $post, $comment_text);
        } catch (EmeraldModelLoadException $e) {
            return $this->response_error(My_Core::RESPONSE_GENERIC_WRONG_PARAMS);
        }

        return $this->response_success();
    }


}
