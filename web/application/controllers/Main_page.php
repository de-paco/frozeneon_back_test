<?php

use Model\Boosterpack_model;
use Model\Post_model;
use Model\User_model;

require_once(dirname(__FILE__). '/../services/LoginService.php');
require_once(dirname(__FILE__). '/../helpers/LoginFormHelper.php');
require_once(dirname(__FILE__). '/../helpers/CommentFormHelper.php');
require_once(dirname(__FILE__). '/../helpers/PostFormHelper.php');
require_once(dirname(__FILE__). '/../services/CommentService.php');
require_once(dirname(__FILE__). '/../services/LikeService.php');

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
        if (User_model::is_logged()) {
            return $this->response_error(\Helpers\LoginFormHelper::getErrorMessage('already_logged'), [], 400);
        }

        $data = \Services\LoginService::login($_POST);

        if (isset($data['errorCode'])) {
            return $this->response_error(\Helpers\LoginFormHelper::getErrorMessage($data['errorCode']), [], 400);
        }

        if (!isset($data['user'])) {
            return $this->response_error(\Helpers\LoginFormHelper::getErrorMessage('default_error'), [], 400);
        }

        \Model\Login_model::login($data['user']);

        return $this->response_success(['user' => true]);
    }

    public function logout()
    {
        \Model\Login_model::logout();
        redirect('/');
    }

    public function comment()
    {
        if (!User_model::is_logged()) {
            return $this->response_error(\Helpers\CommentFormHelper::getErrorMessage('not_auth'), [], 400);
        }

        $data = \Services\CommentService::getOnePostById($_POST);

        if (isset($data['errorMsg'])) {
            return $this->response_error($data['errorMsg'], [], 400);
        }

        return $this->response_success(['comment' => $data['comment']]);
    }

    public function like_comment(int $comment_id)
    {
        if (!User_model::is_logged()) {
            return $this->response_error(\Helpers\CommentFormHelper::getErrorMessage('not_auth'), [], 400);
        }

        $data = \Services\LikeService::likeComment($comment_id, User_model::get_user());

        if (isset($data['errorCode'])) {
            return $this->response_error(\Helpers\PostFormHelper::getErrorMessage($data['errorCode']), [], 400);
        }

        return $this->response_success($data);
    }

    public function like_post(int $post_id)
    {
        if (!User_model::is_logged()) {
            return $this->response_error(\Helpers\CommentFormHelper::getErrorMessage('not_auth'), [], 400);
        }

        $data = \Services\LikeService::likePost($post_id, User_model::get_user());

        if (isset($data['errorCode'])) {
            return $this->response_error(\Helpers\PostFormHelper::getErrorMessage($data['errorCode']), [], 400);
        }

        return $this->response_success($data);
    }

    public function add_money()
    {
        // TODO: task 4, пополнение баланса

        $sum = (float)App::get_ci()->input->post('sum');

    }

    public function get_post(int $post_id)
    {
        $post = \Model\Post_model::getOneById($post_id) ?? null;

        if (empty($post)) {
            return $this->response_error('Post not found');
        }

        $user = User_model::getUserDataByUserId((int)$post['user_id']) ?? null;
        $post['user'] = $user;
        $post['coments'] = \Model\Comment_model::getAllByPostId($post['id']);

        return $this->response_success(['post' => $post]);
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
