<?php
namespace NeatekTinkoff\NeatekTinkoff;

use PDO;

class NeatekTinkoff
{
    const API_URL  = 'https://securepay.tinkoff.ru/v2/';
    const INIT_URL = self::API_URL . 'Init/';
    const DEBUG    = 1;

    /* database config */
    /**
     * @var array
     */
    protected $_db_params = array(
        'db_name' => '',
        'db_host' => '',
        'db_user' => '',
        'db_pass' => '',
    );

    /**
     * @var array
     */
    protected $_params = array(
        'TerminalKey' => '',
        'Password'    => '',
    );
    /**
     * @var array
     */
    protected $_order = array();

    /**
     * @var string
     */
    protected $_last_result = '';

    /**
     * @param array $params
     */
    public function __construct($params = array())
    {
        $this->params = $params[0];
        if (isset($params[1])) {
            $this->_db_params = $params[1];
        }
    }

    /**
     * @param  $param
     * @return mixed
     */
    private function __getParam($param = 'Password')
    {
        if (isset($this->params[$param]) && !empty($this->params[$param])) {
            return $this->params[$param];
        }

        return false;
    }

    /**
     * @param $text
     * @param $code
     */
    public function show_error($text = '', $code = 404)
    {
        echo '<!-- ERROR --> <div style="width:90%;margin:20px;font-size:18px;color:#000;background:#FCF0F0;border:2px #D46363 solid;padding:20px;"><h3>Error ' . $code . '</h3><pre>' . $text . '</pre></div> <!-- ERROR -->';
        http_response_code($code);
        die();
    }

    /**
     * @param $api
     * @param array  $params
     */
    private function __do_request($api = '', $params = array())
    {

        if (empty($api) || empty($params)) {
            return false;
        }

        if ($curl = curl_init()) {
            if (is_array($params)) {
                $params = http_build_query($params);
            }

            curl_setopt($curl, CURLOPT_URL, $api);
            curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $params);
            curl_setopt($curl, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen($params),
            ));

            $result   = curl_exec($curl);
            $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            curl_close($curl);

            if (empty($result)) {
                $this->show_error(
                    'Can not create connection to ' . $api . ' with args '
                    . print_r($this->order, true), $httpcode
                );
            }

            if (!empty($result) && $httpcode == 200) {
                $result = json_decode($result);
            }

            if (isset($result->ErrorCode) && !empty($result->ErrorCode)) {
                $this->show_error(
                    print_r($result->Message . "\r\n" . print_r($this->order, true), true), $result->ErrorCode
                );
            } else {
                $this->__updatePayment($result->OrderId, $result->Status, $result->PaymentId, $result->Amount, $result->PaymentURL);
            }

            if (self::DEBUG > 0) {
                $this->debug($params, '__do_request #' . $result->OrderId);
            }

            $this->_last_result = $result;
            return array($result, $httpcode);
        }

        return false;
    }

    /**
     * @return mixed
     */
    public function GetRedirectURL()
    {
        if (!empty($this->_last_result)) {
            return $this->_last_result->PaymentURL;
        }

        return false;
    }

    /**
     * @param array $params
     */
    public function genToken($params = array())
    {
        if (isset($params['DATA'])) {
            unset($params['DATA']);
        }

        if (isset($params['Receipt'])) {
            unset($params['Receipt']);
        }

        if (isset($params['Items'])) {
            unset($params['Items']);
        }

        if (!empty($this->__getParam('Password'))) {
            $params['Password']    = $this->__getParam('Password');
            $params['TerminalKey'] = $this->__getParam('TerminalKey');
            ksort($params);
            $x = implode('', $params);
            //$this->pre($x);
            return hash('sha256', $x);
        }

        return false;
    }

    /**
     * @param $data
     */
    public function pre($data = '')
    {
        echo '<pre>' . print_r($data, true) . '</pre>';
    }

    /**
     * @return mixed
     */
    public function getDesc()
    {
        if (!isset($this->order['Description'])) {
            return '';
        }

        if (!empty($this->order['Description'])) {
            return $this->order['Description'];
        }

        return '';
    }

    /**
     * @return mixed
     */
    public function getEmail()
    {
        if (!isset($this->order['DATA']['Email'])) {
            return '';
        }

        if (!empty($this->order['DATA']['Email'])) {
            return $this->order['DATA']['Email'];
        }

        return '';
    }

    /**
     * @param $id
     */
    public function setOrderId($id = 0)
    {
        $this->order['OrderId'] = $id;
    }

    /**
     * @param array $params
     */
    public function Init($params = array())
    {
        if (empty($params)) {
            $params = $this->order;
        }

        //$params = array('TerminalKey' => $this->__getParam('TerminalKey')) + $params;
        //$params = array('Token' => $this->genToken($params)) + $params;

        $params['TerminalKey'] = $this->__getParam('TerminalKey');
        $params['Token']       = $this->genToken($params);

        if (isset($params['Receipt']) && !is_object($params['Receipt'])) {
            $params['Receipt'] = (object)$params['Receipt'];
        }

        if (isset($params['DATA']) && !is_object($params['DATA'])) {
            $params['DATA'] = (object)$params['DATA'];
        }

        if (!isset($params['Recurrent'])) {
            $params['Recurrent'] = 'N';
        }

        if ($this->__db_available()) {
            $OrderId = $this->__InsertPayment('WAIT_API', 0, 0, $this->getEmail(), $this->getDesc(), '', $this->genToken($params), $params['Recurrent']);
            $this->setOrderId($OrderId);
            $params['OrderId'] = $OrderId;
        }

        if (
            isset($params['TerminalKey']) &&
            isset($params['Amount']) &&
            isset($params['OrderId']) &&
            isset($params['TerminalKey']) &&
            isset($params['DATA']) &&
            isset($params['Receipt'])
        ) {
            if (self::DEBUG > 0) {
                $this->debug($params, 'Init #' . $params['OrderId']);
            }

            return $this->__do_request(self::INIT_URL, json_encode($params, JSON_UNESCAPED_UNICODE));
        } else {
            $this->show_error('Please fill data: TerminalKey, Amount, OrderId, TerminalKey, DATA, Receipt.<br>Current params:<br>' . print_r($params, true), 415);
        }

        return false;
    }

    public function SetRecurrent()
    {
        $this->order['Recurrent'] = 'Y';
    }

    /**
     * @param array $params
     */
    public function AddMainInfo($params = array())
    {
        $this->order = $params;
    }

    /**
     * @param array $params
     */
    public function AddDATA($params = array())
    {
        $this->order['DATA'] = $params;
    }

    /**
     * @param array $params
     */
    public function AddReceipt($params = array())
    {
        $this->order['Receipt'] = $params;
    }

    /**
     * @param array $params
     */
    public function AddItem($params = array())
    {
        if (!is_array($params)) {
            return;
        }

        if (strlen($params['Name']) > 128) {
            $params['Name'] = substr($params['Name'], 0, 127);
        }

        if (!isset($params['Name']) && !isset($params['Price']) ||
            !isset($params['Quantity']) || !isset($params['Tax'])) {
            return;
        }

        if (!isset($this->order['Receipt']['Items'])) {
            $this->order['Receipt']['Items'] = array();
        }

        $params['Amount']                  = $params['Price'] * $params['Quantity'];
        $this->order['Receipt']['Items'][] = (object)$params;
        $this->CalcAmount();
    }

    /**
     * @return null
     */
    public function CalcAmount()
    {
        if (!isset($this->order['Receipt']['Items'])) {
            $this->order['Amount'] = 0;
            return;
        }

        $amount = 0;
        if (is_array($this->order['Receipt']['Items'])) {
            foreach ($this->order['Receipt']['Items'] as $k => $item) {
                if (isset($item->Amount)) {
                    $amount += $item->Amount; // * $item->Quantity;
                }
            }
        }

        if (!isset($this->order['Amount'])) {
            $this->order = array('Amount' => $amount) + $this->order;
        } else {
            $this->order['Amount'] = $amount;
        }
    }

    /**
     * @param  $mobile
     * @return null
     */
    public function SetOrderMobile($mobile = '')
    {
        if (empty($mobile)) {
            return;
        }

        if (!isset($this->order['Receipt'])) {
            $this->order['Receipt'] = array();
        }

        if (!isset($this->order['DATA'])) {
            $this->order['DATA'] = array();
        }

        $this->order['DATA']['Phone']    = $mobile;
        $this->order['Receipt']['Phone'] = $mobile;
    }

    /**
     * @param $email
     */
    public function SetOrderEmail($email = '')
    {
        if (empty($email)) {
            return;
        }

        if (!isset($this->order['Receipt'])) {
            $this->order['Receipt'] = array();
        }

        if (!isset($this->order['DATA'])) {
            $this->order['DATA'] = array();
        }

        $this->order['DATA']['Email']    = $email;
        $this->order['Receipt']['Email'] = $email;
    }

    /**
     * @param $index
     */
    public function DeleteItem($index = 0)
    {
        if (!isset($this->order['Receipt']['Items'])) {
            return;
        }

        if (isset($this->order['Receipt']['Items'][$index])) {
            unset($this->order['Receipt']['Items'][$index]);
            array_multisort($this->order['Receipt']['Items'], SORT_DESC);
            $this->CalcAmount();
        }
    }

    /**
     * @return array
     */
    public static function AvailableTaxation()
    {
        $av = array(
            'osn',
            'usn_income',
            'usn_income_outcome',
            'envd',
            'esn',
            'patent',
        );
        return (array)$av;
    }

    /**
     * @return array
     */
    public static function AvaliableTax()
    {
        $av = array(
            'none',
            'vat0',
            'vat10',
            'vat18',
            'vat110',
            'vat118',
        );
        return (array)$av;
    }

    /**
     * @param  $tax
     * @return bool
     */
    public function isTaxation($tax = '')
    {
        if (empty($tax)) {
            return false;
        }

        if (in_array($tax, $this->AvailableTaxation())) {
            return true;
        }

        return false;
    }

    /**
     * @param  $tax
     * @return bool
     */
    public function isTax($tax = '')
    {
        if (empty($tax)) {
            return false;
        }

        if (in_array($tax, $this->AvaliableTax())) {
            return true;
        }

        return false;
    }

    /**
     * @param $tax
     */
    public function SetTax($tax = '')
    {
        if (!$this->isTax($tax)) {
            return;
        }

        if (!isset($this->order['Receipt']['Items'])) {
            return;
        }

        if (!empty($this->order['Receipt']['Items']) && is_array($this->order['Receipt']['Items'])) {
            foreach ($this->order['Receipt']['Items'] as $k => $item) {
                if (is_object($item)) {
                    $item = (array)$item;
                }

                $item['Tax']                         = $tax;
                $this->order['Receipt']['Items'][$k] = (object)$item;
            }
        }
    }

    public function showorder()
    {
        $this->pre($this->order);
    }

    /**
     * @param $taxa
     */
    public function SetTaxation($tax = '')
    {
        if (!isset($this->order['Receipt'])) {
            return;
        }

        if (!$this->isTaxation($tax)) {
            return;
        }

        $this->order['Receipt']['Taxation'] = $tax;
    }

    /**
     * @return mixed
     */
    private function __getConnection()
    {
        try {
            $username   = $this->_db_params['db_user'];
            $password   = $this->_db_params['db_pass'];
            $host       = $this->_db_params['db_host'];
            $db         = $this->_db_params['db_name'];
            $connection = new PDO("mysql:dbname=$db;host=$host", $username, $password, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
            $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $connection;
        } catch (PDOException $e) {
            $this->show_error('Подключение не удалось: ' . $e->getMessage());
        }
    }

    /**
     * @param $status
     * @param $paymentId
     * @param $Amount
     * @param $Email
     * @param $desc
     * @param $redirect
     */
    private function __InsertPayment($status = '', $paymentId = 0, $Amount = 0, $Email = '', $desc = '', $redirect = '', $token = '', $Recurrent = 'N')
    {
        if (!$this->__db_available()) {
            return;
        }

        $conn = $this->__getConnection();

        $sql = "INSERT INTO `payments` (`status`, `PaymentId`, `Amount`, `Email`, `Description`, `Redirect`, `Token`, `Recurrent`) VALUES (:status, :paymentId, :amount, :email, :description, :redirect, :token, :recurrent)";

        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':status', $status, PDO::PARAM_STR);
        $stmt->bindParam(':paymentId', $paymentId, PDO::PARAM_INT);
        $stmt->bindParam(':amount', $Amount, PDO::PARAM_INT);
        $stmt->bindParam(':email', $Email, PDO::PARAM_STR);
        $stmt->bindParam(':description', $desc, PDO::PARAM_STR);
        $stmt->bindParam(':redirect', $redirect, PDO::PARAM_STR);
        $stmt->bindParam(':token', $token, PDO::PARAM_STR);
        $stmt->bindParam(':recurrent', $Recurrent, PDO::PARAM_STR);
        $stmt->execute();

        $last_id = (int)$conn->lastInsertId();

        if (self::DEBUG > 0) {
            $this->debug($sql, '__InsertPayment #OrderId:' . $last_id);
        }

        return $last_id;
    }

    /**
     * @param $OrderId
     * @param $paymentId
     * @param $Amount
     * @param $redirect
     */
    private function __updatePayment($OrderId = 0, $status = 'NEW', $paymentId = 0, $Amount = 0, $redirect = '')
    {
        if (!$this->__db_available()) {
            return;
        }

        $conn = $this->__getConnection();

        $sql = "UPDATE `payments` SET `status`=:status, `PaymentId`=:paymentId, `Amount`=:amount, `Redirect`=:redirect WHERE `order_id`=:orderid";

        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':status', $status, PDO::PARAM_STR);
        $stmt->bindParam(':paymentId', $paymentId, PDO::PARAM_INT);
        $stmt->bindParam(':amount', $Amount, PDO::PARAM_INT);
        $stmt->bindParam(':redirect', $redirect, PDO::PARAM_STR);
        $stmt->bindParam(':orderid', $OrderId, PDO::PARAM_INT);
        $stmt->execute();

        if (self::DEBUG > 0) {
            $this->debug($sql, '__updatePayment #OrderId:' . $OrderId);
        }
    }

    private function __db_available()
    {
        if (!empty($this->_db_params) && is_array($this->_db_params) && !empty($this->_db_params['db_name'])) {
            return true;
        }

        return false;
    }

    /**
     * @param  $OrderId
     * @param  $status
     * @param  $CardId
     * @param  $Pan
     * @param  $ExpDate
     * @return null
     */
    private function __updateResultPayment($OrderId = 0, $status = 'NEW', $CardId = 0, $Pan = '', $ExpDate = 0, $RebillId = 0)
    {
        if (!$this->__db_available()) {
            return;
        }

        $conn = $this->__getConnection();

        $sql  = "UPDATE `payments` SET `status`=:status, `CardId`=:CardId, `Pan`=:Pan, `ExpDate`=:ExpDate, `RebillId`=:RebillId WHERE `order_id`=:orderid";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':status', $status, PDO::PARAM_STR);
        $stmt->bindParam(':CardId', $CardId, PDO::PARAM_INT);
        $stmt->bindParam(':Pan', $Pan, PDO::PARAM_STR);
        $stmt->bindParam(':ExpDate', $ExpDate, PDO::PARAM_INT);
        $stmt->bindParam(':RebillId', $RebillId, PDO::PARAM_INT);
        $stmt->bindParam(':orderid', $OrderId, PDO::PARAM_INT);
        $stmt->execute();

        if (self::DEBUG > 0) {
            $this->debug($sql, '__updateResultPayment #OrderId:' . $OrderId);
        }

    }

    public function doRedirect()
    {
        if (!empty($this->GetRedirectURL())) {
            header("X-Redirect: Powered by neatek");
            header("Location: " . $this->GetRedirectURL());
            die();
        }

        die('Empty ->GetRedirectURL();');
    }

    /**
     * @param array $params
     */
    public function checkResultResponse($params = array())
    {
        if (!is_array($params)) {
            $params = (array)$params;
        }

        $prev_token = $params['Token'];

        $params['Success'] = (int)$params['Success'];
        if ($params['Success'] > 0) {
            $params['Success'] = (string) 'true';
        } else {
            $params['Success'] = (string) 'false';
        }

        unset($params['Token']);

        $params['Password']    = $this->__getParam('Password');
        $params['TerminalKey'] = $this->__getParam('TerminalKey');

        ksort($params);
        $x = implode('', $params);

        if (self::DEBUG > 0) {
            $this->debug($params, 'GENERATED_SHA');
            $this->debug('NeededSHA: ' . $prev_token . PHP_EOL . 'ResultSHA:  [' . $x . ']  ' . hash('sha256', $x));
        }

        if (strcmp(strtolower($prev_token), strtolower(hash('sha256', $x))) == 0) {
            if (self::DEBUG > 0) {
                $this->debug('Fully valid sha256.', 'SUCCESS_CHECK_SHA256');
            }

            return true;
        }
    }

    /**
     * @return mixed
     */
    public function getResultResponse()
    {
        $response = file_get_contents('php://input');
        if (!empty($response)) {
            $response = json_decode($response);

            if (self::DEBUG > 0) {
                $this->debug($response);
            }

            if ($this->checkResultResponse($response)) {
                if ($this->__db_available()) {
                    if (!isset($response->RebillId)) {
                        $this->__updateResultPayment($response->OrderId, $response->Status, $response->CardId, $response->Pan, $response->ExpDate, 0);
                    } else {
                        $this->__updateResultPayment($response->OrderId, $response->Status, $response->CardId, $response->Pan, $response->ExpDate, $response->RebillId);
                    }
                }
            }
        }

        echo "OK";
        die();
    }

    /**
     * @param array   $data
     * @param $name
     */
    public function debug($data = array(), $name = '')
    {
        if (self::DEBUG > 0) {
            file_put_contents('debug' . date('dmY') . '.log', date('[H:i:s] ') . $name . "\r\nRESULT:::\r\n" . print_r($data, true) . "\r\n=====\r\n", FILE_APPEND);
        }
    }
}
