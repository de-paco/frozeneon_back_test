<?php

namespace Helpers;

class LoginFormHelper
{
    const errors = [
        'not_found_user' => 'User with this email not found',
        'bad_credentials' => 'Incorrect login credentials, please check your email and password',
        'empty_credentials' => 'Please enter your email and password',
        'already_logged' => 'Please enter your email and password',
        'invalid_email' => 'Please enter correct email',
        'invalid_password' => 'Please enter correct password. Password length must be 3 or more symblos!',
        'default_error' => 'Something wrong! Please try again later',
    ];

    /**
     * @param string $errorCode
     * @return string
     */
    public static function getErrorMessage(string $errorCode): string
    {
        $defaultError = self::errors['default_error'];

        if (empty($errorCode)) {
            return $defaultError;
        }

        return self::errors[$errorCode] ?? $defaultError;
    }
}