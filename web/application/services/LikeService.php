<?php

namespace Services;

use Cake\Database\Exception;
use http\Client\Curl\User;
use Model\Analytics_model;
use Model\Comment_model;
use Model\Post_model;
use Model\User_model;

require_once(dirname(__FILE__). '/../services/ValidateService.php');

class LikeService
{
   public static function likePost($postId, User_model $user)
   {
        $data = [];

        if (!Post_model::checkIfExist($postId)) {
            $data['errorCode'] = 'post_not_found';
        }

        if ($user->get_likes_balance() < 1) {
            $data['errorCode'] = 'balance_null';
        }

        if (!isset($data['errorCode'])) {
            $data['likes'] = self::makeLikePost((int)$postId, $user);
        }

        return $data;
   }

    public static function likeComment($postId, User_model $user)
    {
        $data = [];

        if (!Comment_model::checkIfExist($postId)) {
            $data['errorCode'] = 'post_not_found';
        }

        if ($user->get_likes_balance() < 1) {
            $data['errorCode'] = 'balance_null';
        }

        $data['likes'] = self::makeLikeComment((int)$postId, $user);

        return $data;
    }

    private static function makeLikeComment(int $commentId, User_model $user)
    {
        $likes = null;

        \App::get_s()->set_transaction_repeatable_read()->execute();
        \App::get_s()->start_trans()->execute();

        try {
            $comment = Comment_model::getOneById($commentId);

            if (!$comment->increment_likes($commentId)) {
                throw new Exception('Not affected');
            }

            if (!$user->decrement_likes()) {
                throw new Exception('Not affected');
            }

            $likes = $comment->reload()->get_likes();

            \App::get_s()->commit()->execute();
        } catch (Exception $e) {
            \App::get_s()->rollback()->execute();
            var_dump($e);
        }

        return $likes;
    }

   private static function makeLikePost(int $postId, User_model $user)
   {
       $likes = null;

       \App::get_s()->set_transaction_repeatable_read()->execute();
       \App::get_s()->start_trans()->execute();

       try {
           $post = Post_model::get_by_id($postId);

           if (!$post->increment_likes($postId)) {
               throw new Exception('Not affected');
           }

           if (!$user->decrement_likes()) {
               throw new Exception('Not affected');
           }

           $likes = $post->reload()->get_likes();

           \App::get_s()->commit()->execute();
       } catch (Exception $e) {
           \App::get_s()->rollback()->execute();
           var_dump($e);
       }

       return $likes;
   }
}