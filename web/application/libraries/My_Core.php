<?php

namespace Library;

use System\Libraries\Core;

class My_Core extends Core
{
    const RESPONSE_GENERIC_ALLREADY_LOGGED = 'allready_logged'; // Если уже авторизован
    const RESPONSE_GENERIC_AUTH_LOGGED = 'auth_error'; // Ошибка авторизации

    const RESPONSE_GENERIC_NO_MONEY = 'no_money';
}
