## tinkoff.class.php
1. first-step - Edit tinkoff.params.php

```php
require_once 'tinkoff.params.php';
$tinkoff->AddMainInfo(
    array(
        'OrderId'     => 1, // Не будет работать при подключении к БД, будет автоматически ставиться свой номер заказа из базы данных, рекомендуется всегда оставлять значение = 1 при использовании PDO DB
        'Description' => 'Описание заказа до 250 символов', // Описание заказа
        'Language'    => 'ru', // Язык интерфейса Тинькофф
    )
);
$tinkoff->SetRecurrent(); // Указать что рекуррентный платёж, можно не указывать
$tinkoff->AddItem(
    array(
        'Name'     => 'Название товара 128 символов', // Максимум 128 символов
        'Price'    => 100, // В копейках
        "Quantity" => (float) 1.00, // Вес или количество
        "Tax"      => "none", // В чеке НДС
    )
);
$tinkoff->SetOrderEmail('neatek@icloud.com'); // Обязательно указать емайл
//$tinkoff->SetOrderMobile('+79999999999'); // Установить мобильный телефон
$tinkoff->SetTaxation('usn_income'); // Тип налогообложения 
//$tinkoff->DeleteItem(0); // Можно удалить товар по индексу
$tinkoff->Init(); // Инициализация заказа, и запись в БД если прописаны настройки
$tinkoff->doRedirect(); // Переадресация на оплату заказа
```

2. Notify file

```php
require_once 'tinkoff.params.php';
$tinkoff->getResultResponse(); // Ответ на нотификации
```

3. Params(config) file

```php
use NeatekTinkoff\NeatekTinkoff\NeatekTinkoff;
require_once 'tinkoff.class.php';
$tinkoff = new NeatekTinkoff(
    array(
        array(
            'TerminalKey' => '', // Терминал
            'Password'    => '', // Пароль
        ),
        array(
            // Подключение к БД через PDO
            'db_name' => '',
            'db_host' => '',
            'db_user' => '',
            'db_pass' => '',
        ),
    )
);

```