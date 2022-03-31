<?php

use Model\Analytics_model;
use Model\Boosterpack_model;
use Model\Comment_model;
use Model\Enum\Transaction_info;
use Model\Item_model;
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

        return $this->response_success();
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

        $comment = Comment_model::create_and_preparation($postId, $commentText);

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

        App::get_s()->set_transaction_repeatable_read()->execute();
        App::get_s()->start_trans()->execute();

        try {
            $comment = (new Comment_model())->set_id($commentId)->reload();
            if (!$comment->increment_likes()) {
                throw new Exception('Not affected');
            }

            if (!$currentUser->decrement_likes()) {
                throw new Exception('Not affected');
            }

            Analytics_model::create_remove($currentUser->get_id(), 1, Transaction_info::LIKES_COMMENT, $comment->get_id());

            $likes = $comment->reload()->get_likes();

            App::get_s()->commit()->execute();
        } catch (Throwable $throwable) {
            App::get_s()->rollback()->execute();

            throw $throwable;
        }

        return $this->response_success(['likes' => (int)$likes]);
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

        App::get_s()->set_transaction_repeatable_read()->execute();
        App::get_s()->start_trans()->execute();

        try {
            $post = (new Post_model())->set_id($post_id)->reload();
            if (!$post->increment_likes()) {
                throw new Exception('Not affected');
            }

            if (!$currentUser->decrement_likes()) {
                throw new Exception('Not affected');
            }

            Analytics_model::create_remove($currentUser->get_id(), 1, Transaction_info::LIKES_POST, $post->get_id());

            $likes = $post->reload()->get_likes();

            App::get_s()->commit()->execute();
        } catch (Throwable $throwable) {
            App::get_s()->rollback()->execute();

            throw $throwable;
        }

        return $this->response_success(['likes' => (int)$likes]);
    }

    public function add_money()
    {
        if (!User_model::is_logged()) {
            return $this->response_error(System\Libraries\Core::RESPONSE_GENERIC_NEED_AUTH);
        }

        // Момент получения параметра sum довольно спорный. Как действовать правильно - точно не знаю, т.к. опыта работы с подобными системами нет
        // Моя логика такая: лучше у пользователя спишется меньше на один цент, чем больше на один
        $sum = (float)App::get_ci()->input->post('sum');

        // При огромных числах float, число будем преобразовано в int 0, как обработать это корректно - пока не знаю
        $roundSum = floor($sum * 100) / 100;
        if ($roundSum <= 0) {
            return $this->response_error(System\Libraries\Core::RESPONSE_GENERIC_WRONG_PARAMS);
        }

        App::get_s()->set_transaction_serializable()->execute();
        App::get_s()->start_trans()->execute();

        try {
            $currentUser = User_model::get_user();
            if (!$currentUser->add_money($roundSum)) {
                throw new Exception('Not affected');
            }

            Analytics_model::create_add($currentUser->get_id(), floor($roundSum), Transaction_info::WALLET, $currentUser->get_id());

            $walletBalance = $currentUser->reload()->get_wallet_balance();

            App::get_s()->commit()->execute();
        } catch (Throwable $throwable) {
            App::get_s()->rollback()->execute();

            throw $throwable;
        }

        return $this->response_success(['wallet_balance' => $walletBalance]);
    }

    public function get_post(int $post_id) {
        $post = Post_model::get_full_by_id($post_id);

        return $this->response_success(['post' => $post]);
    }

    public function buy_boosterpack()
    {
        if (!User_model::is_logged()) {
            return $this->response_error(System\Libraries\Core::RESPONSE_GENERIC_NEED_AUTH);
        }

        $id = App::get_ci()->input->post('id');
        if (!is_numeric($id)) {
            return $this->response_error(System\Libraries\Core::RESPONSE_GENERIC_WRONG_PARAMS);
        }

        App::get_s()->set_transaction_serializable()->execute();
        App::get_s()->start_trans()->execute();

        try {
            $boosterpack = Boosterpack_model::get_by_id($id);
            if ($boosterpack === null) {
                return $this->response_info(['error' => 'Boosterpack not found']);
            }

            $currentUser = User_model::get_user();
            if ($currentUser->get_wallet_balance() < $boosterpack->get_price()) {
                return $this->response_info(['error' => 'You don\'t have money']);
            }

            $maxItemPrice = $boosterpack->get_bank() + $boosterpack->get_price() - $boosterpack->get_us();
            $availableItems = Item_model::find_by_less_equals_price($maxItemPrice);
            if (empty($availableItems)) {
                return $this->response_info(['error' => 'You don\'t have available items']);
            }

            $randomItem = $availableItems[rand(0, count($availableItems) - 1)];

            if (!$currentUser->remove_money($boosterpack->get_price())) {
                throw new Exception('Not affected');
            }

            if (!$currentUser->add_likes($randomItem->get_price())) {
                throw new Exception('Not affected');
            }

            Analytics_model::create_remove($currentUser->get_id(), $boosterpack->get_price(), Transaction_info::BOOSTERPACK, $boosterpack->get_id());

            $boosterpack->set_bank($boosterpack->get_bank() + $boosterpack->get_price() - $boosterpack->get_us() - $randomItem->get_price());

            App::get_s()->commit()->execute();
        } catch (Throwable $throwable) {
            App::get_s()->rollback()->execute();

            throw $throwable;
        }

        return $this->response_success(['amount' => $randomItem->get_price()]);
    }





    /**
     * @return object|string|void
     */
    public function get_boosterpack_info(int $bootserpack_info)
    {
        // Check user is authorize
        if (!User_model::is_logged()) {
            return $this->response_error(System\Libraries\Core::RESPONSE_GENERIC_NEED_AUTH);
        }


        //TODO получить содержимое бустерпака
    }
}
