<?php
require_once 'tinkoff.params.php';

$tinkoff->AddMainInfo(
    array(
        'OrderId'     => 1,
        'Description' => 'Описание заказа до 250 символов',
        'Language'    => 'ru',
    )
);

$tinkoff->SetRecurrent();

$tinkoff->AddItem(
    array(
        'Name'     => 'Название товара 128 символов',
        'Price'    => $sum,
        "Quantity" => (float) 1.00,
        "Tax"      => "none",
    )
);
$tinkoff->SetOrderEmail('neatek@icloud.com');
//$tinkoff->SetOrderMobile('+79999999999');
$tinkoff->SetTaxation('usn_income');
//$tinkoff->DeleteItem(0);
$tinkoff->Init();
$tinkoff->doRedirect();
die();
