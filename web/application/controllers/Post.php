<?php

use Library\My_Core;
use Model\Post_model;
use System\Emerald\Exception\EmeraldModelLoadException;

class Post extends MY_Controller
{

    public function __construct()
    {

        parent::__construct();

        if (is_prod()) {
            die('In production it will be hard to debug! Run as development environment!');
        }
    }

    public function get_all_posts()
    {
        $posts = Post_model::preparation_many(Post_model::get_all(), 'default');
        return $this->response_success(['posts' => $posts]);
    }

    public function get_post(int $post_id)
    {
        // Получения поста по id

        try {
            $post = Post_model::find_by_id($post_id);
            $post->is_loaded(TRUE);
        } catch (EmeraldModelLoadException $e) {
            return $this->response_error(My_Core::RESPONSE_GENERIC_WRONG_PARAMS);
        }

        return $this->response_success(['post' => Post_model::preparation($post, 'full_info')]);
    }
}
