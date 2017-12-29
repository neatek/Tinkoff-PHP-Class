<?php
use NeatekTinkoff\NeatekTinkoff\NeatekTinkoff;

require_once 'tinkoff.class.php';

$tinkoff = new NeatekTinkoff(
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
