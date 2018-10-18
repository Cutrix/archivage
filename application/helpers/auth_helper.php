<?php if (defined('BASEPATH') or exit('No direct access allowed'));


if (!function_exists('userDisconnect'))
{
    function userDisconnect($usr)
    {
        return $usr == null;
    }
}