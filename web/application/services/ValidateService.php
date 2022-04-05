<?php

namespace Services;

use Cake\Database\Exception;
use Helpers\CommentServiceHelper;
use Helpers\LoginServiceHelper;
use Interfaces\ValidateServiceInterface;
use Model\User_model;

require_once(dirname(__FILE__). '/../helpers/CommentServiceHelper.php');
require_once(dirname(__FILE__). '/../helpers/LoginServiceHelper.php');
require_once(dirname(__FILE__). '/../helpers/CommentServiceHelper.php');
require_once(dirname(__FILE__). '/../interfaces/ValidateServiceInterface.php');

class ValidateService implements ValidateServiceInterface
{
    /**
     * @param array $data
     * @return false|User_model
     */
    public static function getUser(array $data)
    {
        $post = $data['post'];

        try {
            $user = User_model::find_user_by_email($post['login']);
        } catch (Exception $e) {
            var_dump($e->getMessage());
        }

        if (empty($user)) {
            return false;
        }

        return $user;
    }

    public static function validateCredentials(array $data): bool
    {
        $post = $data['post'];

        $user = User_model::find_user_by_email($post['login']);

        if ($user->get_password_hash() !== self::getHashForPassword($post['password'])) {
            return false;
        }

        return true;
    }

    public static function getHashForPassword(string $password): string
    {
        $salt = 'frozeneon_back_test';

        return md5(md5($password . $salt));
    }

    /**
     * @param array $post
     * @return bool
     */
    public static function validateEmail(array $data): bool
    {

        $post = $data['post'];

        if (!filter_var($post['login'], FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        return true;
    }

    /**
     * @param array $post
     * @return bool
     */
    public static function validatePassword(array $data): bool
    {
        $post = $data['post'];

        $pass = $post['password'];

        if (!is_string($pass) or !is_numeric($pass)) {
            return false;
        }

        if (strlen($post['password']) < 3) {
            return false;
        }

        return true;
    }

    /**
     * @param array $post
     * @return bool
     */
    public static function validateEntityId(array $data): bool
    {
        $post = $data['post'];
        $entityPrefix = $data['entityPrefix'];

        if (!is_numeric($post[$entityPrefix])) {
            return false;
        }

        return true;
    }

    /**
     * @param array $post
     * @return bool
     */
    public static function checkPostData(array $data): bool
    {
        $post = $data['post'];
        $needClass = str_replace(' ', '','Helpers\ '.$data["needServiceHelper"].' ');
        $needParams = call_user_func(array($needClass, 'getNeedParams'));

        foreach ($needParams as $key => $param) {
            if (!in_array($param, array_keys($post))) {
                echo $param;
                return false;
            }
        }

        return true;
    }

    public static function validatePostParams($post, $helperClass, $entityPrefix = null)
    {
        $data = [];

        $needClass = str_replace(' ', '','Helpers\ '.$helperClass.' ');

        $needMethods = call_user_func(array($needClass, 'getNeedMethods'));

        if ($entityPrefix === 'postId') {
            $post[$entityPrefix] = (int)$post[$entityPrefix];
        }

        foreach ($needMethods as $method) {
            try {
                if (!call_user_func(array(self::class, $method['name']),
                    [
                        'post' => $post,
                        'needServiceHelper' => $helperClass,
                        'entityPrefix' => $entityPrefix,
                    ]
                )) {
                    $data['errorCode'] = $method['errorCode'];
                    break;
                }
            } catch (Exception $e) {
                var_dump($e->getMessage());
            }
        }

        if (!$data['errorCode']) {
            $data['user'] = self::getUser(['post' => $post]);
        }

        return $data;
    }
}