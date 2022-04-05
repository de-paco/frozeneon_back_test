<?php

namespace Helpers;

class PostFormHelper
{
    const errors = [
        'not_auth' => 'You are not logged!',
        'default_error' => 'Something wrong! Please try again later',
        'post_not_found' => 'Post not found with this id!',
        'balance_null' => 'You can not like post because you dont have balance!',
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