<?php
    require_once '../classes/MySQLConnector.php';
    require_once '../classes/Date.php';

    $db = new MySQLConnector('localhost', 'leoboey_db', 'methasys2015', 'leoboey_db');

    /*Get time zone*/
    $timeZone = new DateTimeZone('Asia/Kuala_Lumpur');
    date_default_timezone_set('Asia/Kuala_Lumpur');
    $date = date('Y-m-d H:i:s');

    $code = $db->realEscapeString($_REQUEST['code']);

    /*Get today's date*/
    $currentDate = new Date($timeZone);
    $todayDate = $currentDate->getMySQLFormat($timeZone);


    /*Get terminated_mstr count*/
    $result = $db->getResultSet('patient_mstr', ['patient_code','patient_name','patient_fromkk','patient_dose', 'patient_volume'],
        ["patient_code = '$code'", 'patient_m1min = "Y"', 'patient_active = "Y"']);

    $row = $result->fetch_assoc();

    header('Content-Type: application/json');

    $txt = '{';
    $txt .= '"patient_code":"' . $row['patient_code'] . '", ';
    $txt .= '"patient_name":"' . $row['patient_name'] . '", ';
    $txt .= '"patient_fromkk":"' . $row['patient_fromkk'] . '", ';
    $txt .= '"patient_dose":"' . $row['patient_dose'] . '", ';
    $txt .= '"patient_volume":"' . $row['patient_volume'] . '" ';
    $txt .= '}';

    echo $txt;
?>
