## tinkoff.class.php
1. first-step - Edit tinkoff.params.php

```php
require_once 'tinkoff.params.php';
$tinkoff->AddMainInfo(
    array(
        'OrderId'     => '21050', // Will not work if you add Database in tinkoff.params.php
        'Description' => 'Test Payment',
        'Language'    => 'ru',
    )
);
// Here don't add 'Amount', it will be automatically added
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
```

2. Notify file

```php
/***/
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(0);
/***/
header('Content-type: text/plain; charset=utf-8');
header("HTTP/1.1 200 OK");
/***/
require_once 'tinkoff.params.php';
$tinkoff->getResultResponse();
```