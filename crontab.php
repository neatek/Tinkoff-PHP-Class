<?php
require_once 'tinkoff.params.php';
$recurrents = $tinkoff->getLatestForRecurrent();
if (!empty($recurrents)) {
    foreach ($recurrents as $column => $client) {
        /**
         * Params for Init *
         */
        $params = array(
            // Сумма всех
             'Amount'      => (string)$client['Amount'],
            // Номер заказа берется из DB
             'OrderId'     => (string)$client['order_id'],
            // из DB
             'Description' => $client['Description'],
        );
        $params['DATA'] = (object) array(
            'Email' => $client['Email'],
        );
        $params['Receipt'] = (object) array(
            // Береться из DB
             'Email'    => $client['Email'],
            // Налогообложение
             'Taxation' => 'usn_income',
            // С предметами в чеке, можно добавить что вам нужно
             'Items'    => array(
                (object) array(
                    'Name'     => 'Описание товара',
                    'Price'    => $client['Amount'],
                    "Quantity" => 1.00,
                    "Amount"   => $client['Amount'],
                    "Tax"      => "none",
                ),
            ),
        );

        /**
         * Charge - повторый платёж *
         */
        $tinkoff->Charge($tinkoff->Init($params, '[Automatic]'), $client);
    }
}
