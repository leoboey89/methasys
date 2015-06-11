<?php
    require_once '../classes/MySQLConnector.php';
    require_once '../classes/Date.php';

    $db = new MySQLConnector('localhost', 'leoboey_db', 'methasys2015', 'leoboey_db');

    /*Get time zone*/
    $timeZone = new DateTimeZone('Asia/Kuala_Lumpur');
    date_default_timezone_set('Asia/Kuala_Lumpur');
    $date = date('Y-m-d H:i:s');

    $opening = $_REQUEST['opening'];
    $kk = $_COOKIE['kk'];
    $user = $_COOKIE['user'];

    /*Get today's date*/
    $currentDate = new Date($timeZone);
    $todayDate = $currentDate->getMySQLFormat();


    /*Get terminated_mstr count*/
    $result = $db->insertData('stockout_hist', 
        ['stockout_volume', 'stockout_date', 'stockout_kk', 'user_created', 'date_created'],
        [$opening, $todayDate, $kk, $user, $date]);

    header('Content-Type: application/json');

    $txt = '{';
    $txt .= '"insert":"' . $result . '", ';
    $txt .= '"opening":"' . $opening . '" ';
    $txt .= '}';

    echo $txt;
?>
