<?php
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
