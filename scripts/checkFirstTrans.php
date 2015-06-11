<?php

    require_once '../classes/MySQLConnector.php';
    require_once '../classes/Date.php';

    $db = new MySQLConnector('localhost', 'leoboey_db', 'methasys2015', 'leoboey_db');

    /*Get time zone*/
    $timeZone = new DateTimeZone('Asia/Kuala_Lumpur');

    /*Get today's date*/
    $currentDate = new Date($timeZone);
    $todayDate = $currentDate->getMySQLFormat($timeZone);

    $nextDate = new Date($timeZone);
    $nextDate->addDays(1);
    $tomorrowDate = $nextDate->getMySQLFormat($timeZone);
    $kk = $_COOKIE['kk'];


    /*Check if there is any transaction at the day*/
    // $methaScanResult = $db->isExist('methascan_hist', 
    //     ['date_created >= "' . $todayDate . '"', 'date_created < "' . $tomorrowDate . '"']);

    /*Check if opening stock already insert*/
    $stockResult = $db->isExist('stockout_hist', 
        ['stockout_date >= "' . $todayDate . '"', 'stockout_date < "' . $tomorrowDate . '"','stockout_volume > 0', 'stockout_kk = "' . $kk . '"']);

    if ($stockResult) {
        $firstTrans = false;
    } else {
        $firstTrans = true;
    }

    header('Content-Type: application/json');

    $txt = '{';
    $txt .= '"first_trans":"' . $firstTrans . '" ';
    $txt .= '}';

    echo $txt;
?>
