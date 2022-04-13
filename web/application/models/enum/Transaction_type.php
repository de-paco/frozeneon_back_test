<?php
namespace Model\Enum;

use System\Emerald\Emerald_enum;

class Transaction_type extends Emerald_enum {
    const BUY = 'buy';
    const TOPUP = 'topup';
}