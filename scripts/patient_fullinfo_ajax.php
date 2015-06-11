<?php

    require_once '../classes/MySQLConnector.php';
    require_once '../classes/Date.php';

    $code = '';
    $name = '';
    $methatype = '';
    $dose = '';
    $volume = '';
    $status = '';
    $ename = '';
    $econtact = '';
    $attended = false;
    $methascanExist = false;
    $continuouslyAttd = false;
    $terminated = false;

    $db = new MySQLConnector('localhost', 'leoboey_db', 'methasys2015', 'leoboey_db');

    /*Get patient code from javascript and santify it before used in database*/
    $code = trim($_REQUEST['code']);
    $code = strtoupper($db->realEscapeString($code));

    /*Get patient's info*/
    $result = $db->getResultSet('patient_mstr', ['patient_code, patient_name, 
        patient_methatype, patient_dose, patient_volume, patient_status, 
        patient_ename, patient_econtact', 'patient_active', 'patient_mobile',
        'patient_tel', 'patient_email', 'patient_addr1', 'patient_addr2',
        'patient_addr3', 'patient_addr4', 'patient_postcode', 'patient_city',
        'patient_state', 'user_created', 'user_updated', 'date_created', 'date_updated'], 
        ['patient_code = "' . $code . '"']);

    if ($row = $result->fetch_assoc()) {
        $name = $row['patient_name'];
        $methatype = $row['patient_methatype'];
        $dose = $row['patient_dose'];
        $volume = $row['patient_volume'];
        $status = $row['patient_status'];
        $ename = $row['patient_ename'];
        $econtact = $row['patient_econtact'];
        $active = $row['patient_active'];
        $mobile = $row['patient_mobile'];
        $tel = $row['patient_tel'];
        $email = $row['patient_email'];
        $addr1 = $row['patient_addr1'];
        $addr2 = $row['patient_addr2'];
        $addr3 = $row['patient_addr3'];
        $addr4 = $row['patient_addr4'];
        $postcode = $row['patient_postcode'];
        $city = $row['patient_city'];
        $state = $row['patient_state'];
        $userCreated = $row['user_created'];
        $userUpdated = $row['user_updated'];
        $dateCreated = $row['date_created'];
        $dateUpdated = $row['date_updated'];

        $txt = '{';
        $txt .= '"patient_name":"' . $name . '",';
        $txt .= '"patient_code":"' . $code . '",';
        $txt .= '"patient_methatype":"' . $methatype . '",';
        $txt .= '"patient_dose":"' . $dose . '",';
        $txt .= '"patient_volume":"' . $volume . '",';
        $txt .= '"patient_status":"' . $status . '",';
        $txt .= '"patient_ename":"' . $ename . '",';
        $txt .= '"patient_econtact":"' . $econtact . '", ';
        $txt .= '"patient_active":"' . $active . '", ';
        $txt .= '"patient_mobile":"' . $mobile . '", ';
        $txt .= '"patient_tel":"' . $tel . '", ';
        $txt .= '"patient_email":"' . $email . '", ';
        $txt .= '"patient_addr1":"' . $addr1 . '", ';
        $txt .= '"patient_addr2":"' . $addr2 . '", ';
        $txt .= '"patient_addr3":"' . $addr3 . '", ';
        $txt .= '"patient_addr4":"' . $addr4 . '", ';
        $txt .= '"patient_postcode":"' . $postcode . '", ';
        $txt .= '"patient_city":"' . $city . '", ';
        $txt .= '"patient_state":"' . $state . '", ';
        $txt .= '"user_created":"' . $userCreated . '", ';
        $txt .= '"user_updated":"' . $userUpdated . '", ';
        $txt .= '"date_created":"' . $dateCreated . '", ';
        $txt .= '"date_updated":"' . $dateUpdated . '" ';
        $txt .= '}';
    } else {
        $txt = '{';
        $txt .= '"patient_name":"",';
        $txt .= '"patient_code":"",';
        $txt .= '"patient_methatype":"",';
        $txt .= '"patient_dose":"",';
        $txt .= '"patient_volume":"",';
        $txt .= '"patient_status":"",';
        $txt .= '"patient_ename":"",';
        $txt .= '"patient_econtact":"",';
        $txt .= '"patient_active":"",';
        $txt .= '"patient_mobile":"",';
        $txt .= '"patient_tel":"",';
        $txt .= '"patient_email":"",';
        $txt .= '"patient_addr1":"",';
        $txt .= '"patient_addr2":"",';
        $txt .= '"patient_addr3":"",';
        $txt .= '"patient_addr4":"",';
        $txt .= '"patient_postcode":"",';
        $txt .= '"patient_city":"",';
        $txt .= '"patient_state":"",';
        $txt .= '"user_created":"",';
        $txt .= '"user_updated":"",';
        $txt .= '"date_created":"",';
        $txt .= '"date_updated":"" ';
        $txt .= '}';
    }

    echo $txt;
?>
