<?php

namespace Helpers;

class LoginServiceHelper
{
    const needParams = ['login', 'password'];

    const needMethods = [
        0 => [
            'name' => 'checkPostData',
            'errorCode' => 'empty_credentials'
        ],
        1 => [
            'name' => 'validateEmail',
            'errorCode' => 'invalid_email'
        ],
        2 => [
            'name' => 'validatePassword',
            'errorCode' => 'invalid_password'
        ],
        3 => [
            'name' => 'getUser',
            'errorCode' => 'not_found_user'
        ],
        4 => [
            'name' => 'validateCredentials',
            'errorCode' => 'bad_credentials'
        ],
    ];

    /**
     * @return string[]
     */
    public static function getNeedParams(): array
    {
        return self::needParams;
    }

    /**
     * @return string[][]
     */
    public static function getNeedMethods(): array
    {
        return self::needMethods;
    }
}