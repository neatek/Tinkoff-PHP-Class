## tinkoff.class.php
1. first-step - Edit tinkoff.params.php

```php
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
        'Price'    => '100', // В копейках
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
```

2. Notify file

```php
require_once 'tinkoff.params.php';
$tinkoff->getResultResponse();
```

3. Params(config) file

```php
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

```