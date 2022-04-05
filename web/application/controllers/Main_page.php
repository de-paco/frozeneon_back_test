<?php

use Model\Boosterpack_model;
use Model\Comment_model;
use Model\Login_model;
use Model\Post_model;
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

        App::get_ci()->load->view('main_page', ['user' => User_model::preparation($user, 'default')]);
    }

    public function get_all_posts()
    {
        $posts =  Post_model::preparation_many(Post_model::get_all(), 'default');
        return $this->response_success(['posts' => $posts]);
    }

    public function get_boosterpacks()
    {
        $posts =  Boosterpack_model::preparation_many(Boosterpack_model::get_all(), 'default');
        return $this->response_success(['boosterpacks' => $posts]);
    }

    public function login()
    {
        $login = (string) App::get_ci()->input->post('login');
        $password = (string) App::get_ci()->input->post('password');

        $user = Login_model::login($login, $password);

        return $this->response_success(['user' => User_model::preparation($user, 'main_page')]);
    }

    public function logout()
    {
        Login_model::logout();
    }

    public function comment()
    {
        // Check user is authorize
        if (!User_model::is_logged()) {
            return $this->response_error(System\Libraries\Core::RESPONSE_GENERIC_NEED_AUTH);
        }

        $post_id = (int) App::get_ci()->input->post('postId');
        $comment_text = (string) App::get_ci()->input->post('commentText');
        $reply_id = (int) App::get_ci()->input->post('replyId');

        if (!$reply_id) {
            $reply_id = null;
        }

        $comment = Comment_model::add_comment_to_post($post_id, $comment_text, $reply_id);

        return $this->response_success(['comment' => Comment_model::preparation($comment)]);
    }

    public function like_comment(int $comment_id)
    {
        // Check user is authorize
        if ( ! User_model::is_logged())
        {
            return $this->response_error(System\Libraries\Core::RESPONSE_GENERIC_NEED_AUTH);
        }

        $user = User_model::get_user();

        $comment = Comment_model::get_one_by_id($comment_id);

        if (!$comment->increment_likes($user)) {
            return $this->response_error(System\Libraries\Core::RESPONSE_GENERIC_INTERNAL_ERROR);
        }

        return $this->response_success(['likes' => $comment->get_likes()]);
    }

    public function like_post(int $post_id)
    {
        // Check user is authorize
        if (!User_model::is_logged()) {
            return $this->response_error(System\Libraries\Core::RESPONSE_GENERIC_NEED_AUTH);
        }

        $post = Post_model::get_one_by_id($post_id);

        $user = User_model::get_user();

        if (!$post->increment_likes($user)) {
            return $this->response_error(System\Libraries\Core::RESPONSE_GENERIC_INTERNAL_ERROR);
        }

        return $this->response_success(['likes' => $post->get_likes()]);
    }

    public function add_money()
    {
        // Check user is authorize
        if (!User_model::is_logged()) {
            return $this->response_error(System\Libraries\Core::RESPONSE_GENERIC_NEED_AUTH);
        }

        $sum = (float) App::get_ci()->input->post('sum');

        $user = User_model::get_user();

        if (!$user->add_money($sum)) {
            return $this->response_error(System\Libraries\Core::RESPONSE_GENERIC_INTERNAL_ERROR);
        }

        return $this->response_success();
    }

    public function get_post(int $post_id) {
        $post = Post_model::get_one_by_id($post_id);

        return $this->response_success(['post' => Post_model::preparation($post, 'full_info')]);
    }

    public function buy_boosterpack()
    {
        // Check user is authorize
        if ( ! User_model::is_logged())
        {
            return $this->response_error(System\Libraries\Core::RESPONSE_GENERIC_NEED_AUTH);
        }

        // TODO: task 5, покупка и открытие бустерпака
    }





    /**
     * @return object|string|void
     */
    public function get_boosterpack_info(int $bootserpack_info)
    {
        // Check user is authorize
        if ( ! User_model::is_logged())
        {
            return $this->response_error(System\Libraries\Core::RESPONSE_GENERIC_NEED_AUTH);
        }


        //TODO получить содержимое бустерпака
    }
}
