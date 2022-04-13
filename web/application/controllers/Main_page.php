<?php

use Model\Boosterpack_model;
use Model\Comment_model;
use Model\Likeable_model;
use Model\Post_model;
use Model\User_model;
use Model\Login_model;
use System\Emerald\Emerald_model;
use System\Libraries\Core;

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
var_dump($user);
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
        $this->load->helper('form');
        $this->load->library('form_validation');

        $this->form_validation->set_rules('login', 'Login', 'required|valid_email');
        $this->form_validation->set_rules('password', 'Password', 'required');

        if ( ! $this->form_validation->run())
        {
            return $this->response_error(
                Core::RESPONSE_GENERIC_WRONG_PARAMS,
                ['info' => $this->form_validation->error_string()]
            );
        }
        $email = (string)App::get_ci()->input->post('login');
        $password = (string)App::get_ci()->input->post('password');
        $user = User_model::find_user_by_email($email);
        try
        {
            if ( ! $user->is_loaded())
            {
                return $this->response_error(Core::RESPONSE_GENERIC_WRONG_PARAMS);
            }
            if ($user->get_password() != $password)
            {
                return $this->response_error(Core::RESPONSE_GENERIC_WRONG_PARAMS);
            }
            Login_model::login($user);
        }
        catch (Exception $ex)
        {
            return $this->response_error(
                Core::RESPONSE_GENERIC_INTERNAL_ERROR,
                ['info' => $ex->getMessage()]
            );
        }

        return $this->response(['user' => $user]);
    }

    public function logout()
    {
        Login_model::logout();

        redirect('/');
    }

    public function comment()
    {
        if ( ! User_model::is_logged())
        {
            return $this->response_error(Core::RESPONSE_GENERIC_NEED_AUTH);
        }

        $this->load->helper('form');
        $this->load->library('form_validation');

        $this->form_validation->set_rules('postId', 'PostId', 'required');
        $this->form_validation->set_rules('commentText', 'Text', 'required');

        $post_id = (int)App::get_ci()->input->post('postId');
        $comment_text = (string)App::get_ci()->input->post('commentText');

        try
        {
            $comment = Comment_model::create([
                'user_id'   => User_model::get_user()->get_id(),
                'assign_id' => $post_id,
                'text'      => $comment_text,
                'likes'     => 0,
            ]);

            $comment = Comment_model::preparation($comment);
        }
        catch (Exception $ex)
        {
            return $this->response_error(
                Core::RESPONSE_GENERIC_INTERNAL_ERROR,
                ['info' => $ex->getMessage()]
            );
        }

        return $this->response(['comment' => $comment]);
    }

    public function like_comment(int $comment_id)
    {
        $comment = new Comment_model($comment_id);

        return $this->like($comment);
    }

    public function like_post(int $post_id)
    {
        $post = new Post_model($post_id);

        return $this->like($post);
    }

    public function add_money()
    {
        if ( ! User_model::is_logged())
        {
            return $this->response_error(Core::RESPONSE_GENERIC_NEED_AUTH);
        }

        $sum = (int)App::get_ci()->input->post('sum');
        $user = User_model::get_user();

        try
        {
            return $user->add_money($sum)
                ? $this->response_success()
                : $this->response_error();
        }
        catch (Exception $ex)
        {
            return $this->response_error($ex->getMessage());
        }
    }

    public function get_post(int $post_id) {
        try
        {
            $post = Post_model::preparation(new Post_model($post_id), 'full_info');
        }
        catch (Exception $ex)
        {
            return $this->response_error($ex->getMessage());
        }

        return $this->response(['post' => $post]);
    }

    public function buy_boosterpack()
    {
        // Check user is authorize
        if ( ! User_model::is_logged())
        {
            return $this->response_error(Core::RESPONSE_GENERIC_NEED_AUTH);
        }

        $this->load->helper('form');
        $this->load->library('form_validation');

        $this->form_validation->set_rules('id', 'BoosterPackId', 'required|numeric');

        $booster_pack_id = (float)App::get_ci()->input->post('id');
        $booster_pack = new Boosterpack_model($booster_pack_id);

        $user = User_model::get_user();
        if ($booster_pack->get_price() > $user->get_wallet_balance())
        {
            return $this->response_error(Core::RESPONSE_GENERIC_WRONG_PARAMS);
        }

        return $this->response(['amount' => $booster_pack->open()]);
    }

    /**
     * @return object|string|void
     */
    public function get_boosterpack_info(int $bootserpack_info)
    {
        // Check user is authorize
        if ( ! User_model::is_logged())
        {
            return $this->response_error(Core::RESPONSE_GENERIC_NEED_AUTH);
        }


        //TODO получить содержимое бустерпака
    }

    private function like(Likeable_model $model)
    {
        if ( ! User_model::is_logged())
        {
            return $this->response_error(Core::RESPONSE_GENERIC_NEED_AUTH);
        }

        $user = User_model::get_user();
        if ( ! $user->get_likes_balance())
        {
            return $this->response_error(Core::RESPONSE_GENERIC_WRONG_PARAMS);
        }

        try
        {
            $res = $model->increment_likes($user);
        }
        catch (Exception $ex)
        {
            return $this->response_error(
                Core::RESPONSE_GENERIC_INTERNAL_ERROR,
                ['info' => $ex->getMessage()]
            );
        }
        return $res
            ? $this->response_success()
            : $this->response_error();
    }
}
