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

    public function get_likes_balance()
    {
        if (!User_model::is_logged()) {
            return $this->response_error(System\Libraries\Core::RESPONSE_GENERIC_NEED_AUTH);
        }

        $user = User_model::get_user();

        return $this->response_success(['likes' => $user->get_likes_balance()]);
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
        // TODO: текст ошибок заменить на константы
        if (User_model::is_logged()) {
            return $this->response_error('You are already logged in');
        }

        $login = App::get_ci()->input->post('login');
        $password = App::get_ci()->input->post('password');
        if (empty($login) || empty($password)) {
            return $this->response_error(System\Libraries\Core::RESPONSE_GENERIC_WRONG_PARAMS);
        }

        $user = User_model::find_user_by_email($login);
        if ($user->get_id() === null) {
            return $this->response_info(['error' => 'invalidLogin']);
        }

        if ($user->get_password() !== $password) {
            return $this->response_info(['error' => 'invalidPass']);
        }

        Login_model::login($user);

        return $this->response_success();
    }

    public function logout()
    {
        Login_model::logout();

        // TODO: пересмотреть, может есть другой вариант, нужен именно редирект. Сделать релоад на фронте?
        $this->index();
    }

    public function comment()
    {
        if (!User_model::is_logged()) {
            return $this->response_error(System\Libraries\Core::RESPONSE_GENERIC_NEED_AUTH);
        }

        $postId = App::get_ci()->input->post('postId');
        $commentText = App::get_ci()->input->post('commentText');
        if (!is_numeric($postId) || empty($commentText)) {
            return $this->response_error(System\Libraries\Core::RESPONSE_GENERIC_WRONG_PARAMS);
        }

        $post = Post_model::exists_by_id($postId);
        if (!$post) {
            return $this->response_error(System\Libraries\Core::RESPONSE_GENERIC_WRONG_PARAMS);
        }

        $comment = Comment_model::create([
            'user_id' => User_model::get_user()->get_id(),
            'assign_id' => $postId,
            'text' => $commentText,
            'likes' => 0,
        ]);
        $comment = Comment_model::preparation($comment);

        return $this->response_success(['comment' => $comment]);
    }

    public function like_comment(int $commentId)
    {
        if (!User_model::is_logged()) {
            return $this->response_error(System\Libraries\Core::RESPONSE_GENERIC_NEED_AUTH);
        }

        $currentUser = User_model::get_user();
        $likesBalance = $currentUser->get_likes_balance();
        if ($likesBalance === 0) {
            return $this->response_info(['error' => 'Likes balance is empty']);
        }

        // TODO: обернуть в транзакцию, пока не нашел как
        // TODO: попробовать найти нормальное решение
        $comment = (new Comment_model())->set_id($commentId)->reload();
        if (!$comment->increment_likes()) {
            throw new Exception('Not affected');
        }

        if (!$currentUser->decrement_likes()) {
            throw new Exception('Not affected');
        }

        return $this->response_success(['likes' => (int)$comment->reload()->get_likes()]);
    }

    public function like_post(int $post_id)
    {
        if (!User_model::is_logged()) {
            return $this->response_error(System\Libraries\Core::RESPONSE_GENERIC_NEED_AUTH);
        }

        $currentUser = User_model::get_user();
        $likesBalance = $currentUser->get_likes_balance();
        if ($likesBalance === 0) {
            return $this->response_info(['error' => 'Likes balance is empty']);
        }

        // TODO: обернуть в транзакцию, пока не нашел как
        // TODO: попробовать найти нормальное решение
        $post = (new Post_model())->set_id($post_id)->reload();
        if (!$post->increment_likes()) {
            throw new Exception('Not affected');
        }

        if (!$currentUser->decrement_likes()) {
            throw new Exception('Not affected');
        }

        return $this->response_success(['likes' => (int)$post->reload()->get_likes()]);
    }

    public function add_money()
    {
        // TODO: task 4, пополнение баланса

        $sum = (float)App::get_ci()->input->post('sum');

    }

    public function get_post(int $post_id) {
        $post = Post_model::get_full_by_id($post_id);

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
