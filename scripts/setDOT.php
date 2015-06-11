<?php
    session_set_cookie_params(0);

    require_once '../classes/MySQLConnector.php';
    require_once '../classes/Date.php';
    require_once '../includes/session.php';

    $db = new MySQLConnector('localhost', 'leoboey_db', 'methasys2015', 'leoboey_db');

    /*Get time zone*/
    $timeZone = new DateTimeZone('Asia/Kuala_Lumpur');
    date_default_timezone_set('Asia/Kuala_Lumpur');
    $date = date('Y-m-d H:i:s');
    
    $patcode = $_REQUEST['patcode'];
    $patname = $_REQUEST['patname'];
    $dose = $_REQUEST['dose'];
    $volume = $_REQUEST['volume'];
    $methatype = $_REQUEST['methatype'];
    $patstatus = $_REQUEST['patstatus'];
    $createBy = $_COOKIE['user'];
    $kk = $_COOKIE['kk'];

    /*Insert DOT into methascan_hist*/
    $methascanResult = $db->insertData('methascan_hist',['methascan_patcode', 'methascan_patname', 
                'methascan_dose', 'methascan_volume', 'methascan_date', 'methascan_methatype',
                'methascan_patstatus', 'methascan_dot', 'methascan_kk', 'user_created', 
                'date_created'],[$patcode, $patname,
                $dose, $volume, $date, $methatype, 
                $patstatus, 'Y', $kk, $createBy, 
                $date]);
    /*Insert DBB into dot_mstr*/
    $dotResult = $db->insertData('dot_mstr',['dot_patcode', 'dot_patname', 
                'dot_active', 'dot_dose', 'dot_volume', 'dot_kk', 
                'user_created', 'date_created'],[$patcode, $patname,
                'Y', $dose, $volume, $kk, 
                $createBy, $date]);

    if ($methascanResult && $dotResult) {
        $result = true;
    } else {
        $result = false;
    }


    header('Content-Type: application/json');

    $txt = '{';
    $txt .= '"insert":"' . $result . '", ';
    $txt .= '"patcode":"' . $patcode . '" ';
    $txt .= '}';

    echo $txt;
?> 