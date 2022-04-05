<?php

namespace Services;

use Helpers\CommentServiceHelper;
use Model\Comment_model;
use Model\Post_model;

require_once(dirname(__FILE__). '/../services/ValidateService.php');
require_once(dirname(__FILE__). '/../helpers/CommentFormHelper.php');

class CommentService extends ValidateService
{
    private static function checkIfExist(int $id)
    {
        $comment = Comment_model::getOneById($id);
        if (empty($comment)) {
            return false;
        }

        return true;
    }


    public static function getOnePostById(array $post)
    {
        $data = [];

        $entityPrefix = 'postId';
        $data = self::validatePostParams($post, 'CommentServiceHelper', $entityPrefix);

        if (!self::checkIfExist($post[$entityPrefix])) {
            $data['errorMsg'] = \Helpers\CommentFormHelper::getErrorMessage('entity_id_error');
        }

        $newComment = \Model\Comment_model::createNew($post[$entityPrefix], $post['commentText']);

        if (empty($newComment)) {
            $data['errorMsg'] = \Helpers\CommentFormHelper::getErrorMessage('default_error');
        }

        $data['comment'] = $newComment;

        return $data;
    }
}