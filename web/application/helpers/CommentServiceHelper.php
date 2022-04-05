<?php

namespace Helpers;

class CommentServiceHelper
{
    const needParams = ['postId', 'commentText'];

    const needMethods = [
        0 => [
            'name' => 'checkPostData',
            'errorCode' => 'empty_credentials'
        ],
        1 => [
            'name' => 'validateEntityId',
            'errorCode' => 'entity_id_error'
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