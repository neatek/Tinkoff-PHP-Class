<?php
header('Content-type: text/html; charset=utf-8');
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
$tinkoff->AddMainInfo(
    array(
        'OrderId'     => '21050',
        'Description' => 'Оплата тестового заказа',
        'Language'    => 'ru',
    )
);
$tinkoff->AddItem(
    array(
        'Name'     => 'Name1',
        'Price'    => 500,
        "Quantity" => (float) 1.00,
        "Tax"      => "none",
    )
);
$tinkoff->SetOrderEmail('mycloud@icloud.com');
$tinkoff->SetOrderMobile('+798777892');
$tinkoff->SetTaxation('usn_income');
//$tinkoff->DeleteItem(0);
$tinkoff->pre($tinkoff->Init());
var_dump($tinkoff->GetRedirectURL());
