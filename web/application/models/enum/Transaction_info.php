<?php

namespace Model\Enum;

use System\Emerald\Emerald_enum;

class Transaction_info extends Emerald_enum
{
    public const WALLET = 'wallet';
    public const BOOSTERPACK = 'boosterpack';
    public const LIKES_COMMENT = 'likes_comment';
    public const LIKES_POST = 'likes_post';
}
