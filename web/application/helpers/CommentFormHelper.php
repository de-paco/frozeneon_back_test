<?php

namespace Helpers;

class CommentFormHelper
{
    const errors = [
        'not_auth' => 'You are not logged!',
        'default_error' => 'Something wrong! Please try again later',
        'entity_id_error' => 'Entity Id must be int',
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