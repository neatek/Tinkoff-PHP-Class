<?php
require_once 'tinkoff.class.php';
use NeatekTinkoff\NeatekTinkoff;
$tinkoff = new NeatekTinkoff\NeatekTinkoff(
    array(
        array(
            'TerminalKey' => '',
            'Password'    => '',
        ),
        array(
            'db_name' => '',
            'db_host' => '',
            'db_user' => '',
            'db_pass' => '',
        ),
    )
);
