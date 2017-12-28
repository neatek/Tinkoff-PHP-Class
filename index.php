<?php
require_once 'tinkoff.params.php';
$tinkoff->AddMainInfo(
    array(
        'OrderId'     => '21050',
        'Description' => 'Test Payment',
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
$tinkoff->AddItem(
    array(
        'Name'     => 'Name1',
        'Price'    => 500,
        "Quantity" => (float) 1.00,
        "Tax"      => "none",
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
$tinkoff->SetOrderEmail('test@mail.ru');
$tinkoff->SetOrderMobile('+79999999999');
$tinkoff->SetTaxation('usn_income');
//$tinkoff->DeleteItem(0);
$tinkoff->Init();
$tinkoff->doRedirect();
