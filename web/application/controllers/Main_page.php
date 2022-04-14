<?php

use Model\Boosterpack_model;
use Model\Post_model;
use Model\User_model;
use Model\Login_model;
use Model\Comment_model;

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
        // TODO: task 1, аутентификация

        // Get provided data
        $login = App::get_ci()->input->post('login');
        $password = App::get_ci()->input->post('password');
        $redirect = App::get_ci()->input->get('redirect');

        // If user already logged in
        if (User_model::is_logged())
        {
            return $this->response_success(['message' => 'This user already logged in']);
        }

        // If login or password not provided
        if(empty($login) || empty($password))
        {
            return $this->response_error('Please provide login and password');
        }

        // Get user data from database
        $user = User_model::find_user_by_email($login);

        // Chec user exist
        if($user->get_id() == 0)
        {
           return $this->response_error("There is not user with login $login");
        }

        // Check password
        if ($user->get_password() != $password)
        {
            return $this->response_error("Wrong password");
        }

        // Authenticate user
        Login_model::login($user);

        // Redirect user
        if (!empty($redirect))
        {
            header("Location: $redirect");
            die();
        }

        // Return success message
        return $this->response_success(["message" => "You are logged in as $login"]);
    }

    public function logout()
    {
        // TODO: task 1, аутентификация

        // Logout user
        Login_model::logout();

        // Redirect user
        $redirect = App::get_ci()->input->get('redirect');
        if (!empty($redirect))
        {
            header("Location: $redirect");
            die();
        }

        // Return success message
        return $this->response_success(["message" => "You are logged out"]);
    }

    public function comment()
    {
        // TODO: task 2, комментирование

        // If user is not logged in
        if (!User_model::is_logged())
        {
            return $this->response_error("You are not logged in");
        }

        // Provided data
        $user = User_model::get_user()->get_id();
        $post = App::get_ci()->input->post('post');
        $text = App::get_ci()->input->post('comment');
        $reply = App::get_ci()->input->post('reply');

        // Check if post exist
        if (!is_numeric($post) || Post_model::exist($post) == 0)
        {
            return $this->response_error("The post $post doesn't exist");
        }

        // Check if comment exists
        if (!empty($reply))
        {
            if (!is_numeric($reply) || Comment_model::exist($reply) == 0)
            {
                return $this->response_error("The comment $reply doesn't exist");
            }
        }
        else 
        {
            $reply = NULL;
        }

        // Check provided data
        if (empty($text))
        {
            return $this->response_error("Comment can't be empty");
        }

        // Create comment
        $comment = Comment_model::create([
            'user_id' => $user,
            'assign_id' => $post,
            'reply_id' => $reply,
            'text' => $text,
            'likes' => 0
        ]);
        $comment = Comment_model::preparation($comment);

        // Return success message
        return $this->response_success(["message" => "Comment $comment->id was created."]);

    }

    public function like_comment(int $comment_id)
    {
        // TODO: task 3, лайк комментария

        // If user is not logged in
        if (!User_model::is_logged())
        {
            return $this->response_error("You are not logged in");
        }

        // Check if comment exist
        if (!is_numeric($comment_id) || Comment_model::exist($comment_id) == 0)
        {
            return $this->response_error("The comment $comment_id doesn't exist");
        }

        // Increment likes and return response
        if (Comment_model::increment_likes($comment_id))
        {
            return $this->response_success("The comment $comment_id was liked");
        }

    }

    public function like_post(int $post_id)
    {
        // TODO: task 3, лайк поста

        // If user is not logged in
        if (!User_model::is_logged())
        {
            return $this->response_error("You are not logged in");
        }

        // Check if post exist
        if (!is_numeric($post_id) || Post_model::exist($post_id) == 0)
        {
            return $this->response_error("The post $post_id doesn't exist");
        }

        // Increment likes and return response
        if (Post_model::increment_likes($post_id))
        {
            return $this->response_success("The post $post_id was liked");
        }

    }

    private $servers = ['192.168.0.104'];

    public function add_money()
    {
        // TODO: task 4, пополнение баланса

        // Get sum from request
        $sum = (float)App::get_ci()->input->post('sum');

        // Check if server is authorised
        if (!in_array($_SERVER['REMOTE_ADDR'], $this->servers))
        {
            return $this->response_error("Server is not authorised");
        }

        // Check if sum is valid
        if ($sum <= 0)
        {
            return $this->response_error("Sum is invalid");
        }

        // 
        if((new User_model)->add_money($sum))
        {
            return $this->response_success(["message" => "Wallet was refilled"]);
        }
        else
        {
            return $this->response_error("Wallet was not refilled");
        }


    }

    public function get_post(int $post_id) {
        // TODO получения поста по id
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
