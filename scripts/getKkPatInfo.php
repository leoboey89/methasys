<?php
    session_set_cookie_params(0);

    require_once '../classes/MySQLConnector.php';
    require_once '../classes/Date.php';
    require_once '../includes/session.php';

    $db = new MySQLConnector('localhost', 'leoboey_db', 'methasys2015', 'leoboey_db');

    $kkCode = $_REQUEST['kkcode'];

    $kkPatResult = $db->getResultSetBySql('SELECT patient_code FROM patient_mstr a left join spubperm_mstr 
        on patient_code = spubperm_patcode where spubperm_patcode is null 
        and patient_kk = "' . $kkCode . '" and patient_active = "Y" 
        and patient_spubin = "N" and patient_m1min = "N"');

    while ($row = $kkPatResult->fetch_assoc()) {
        $txt[] = $row['patient_code'];
    } 

    header('Content-Type: application/json');

    echo json_encode($txt);
?>
