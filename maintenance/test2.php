<?php
    require_once '../classes/Date.php';
    require_once '../classes/MySQLConnector.php';
    require_once '../classes/Validator.php';
    require_once '../includes/session.php';

    $db = new MySQLConnector('localhost', 'leoboey_db', 'methasys2015', 'leoboey_db');

    $code = 'SMB00015';
    $kk = $_COOKIE['kk'];

    /*Get today's date*/
    $timeZone = new DateTimeZone('Asia/Kuala_Lumpur');
    $currentDate = new Date($timeZone);
    $todayDate = $currentDate->getMySQLFormat($timeZone);

    /*Get all SPUB Out patient for update purpose*/
    $spubPatientResult = $db->getResultSet(['spubout_mstr', 'patient_mstr'], 
        ['spubout_patname', 'spubout_patcode'], 
        ['spubout_supplyfrom <= "' . $todayDate . '"', 'spubout_supplyto >= "' . $todayDate . '"', 
        'spubout_active = "Y"', 'spubout_patcode = patient_code', 
        'patient_active = "Y"', 'patient_kk = "' . $kk . '"',
        'spubout_kk = "' . $kk . '"', 'patient_spubout = "Y"'], 
        ['spubout_patcode'], ['spubout_patcode']);

    while($row = $spubPatientResult->fetch_assoc()) {
        echo $row['spubout_patcode'];
    }

    echo "Success";
?>
