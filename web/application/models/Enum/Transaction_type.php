<?php

namespace Model\Enum;

use System\Emerald\Emerald_enum;

class Transaction_type extends Emerald_enum {

    public const ADD = 'withdrawn';
    public const REMOVE = 'refilled';

}