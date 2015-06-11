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

    $purpose = $_REQUEST['purpose'];
    $fromkk = $_REQUEST['fromkk'];
    $patcode = $_REQUEST['patcode'];
    $active = $_REQUEST['active'];
    $from = $_REQUEST['from'];
    $to = $_REQUEST['to'];
    $sunday = $_REQUEST['sunday'];
    $monday = $_REQUEST['monday'];
    $tuesday = $_REQUEST['tuesday'];
    $wednesday = $_REQUEST['wednesday'];
    $thursday = $_REQUEST['thursday'];
    $friday = $_REQUEST['friday'];
    $saturday = $_REQUEST['saturday'];
    $code = $_REQUEST['code'];
    $user = $_COOKIE['user'];

    $date1 = new DateTime($from, new DateTimeZone('Asia/Kuala_Lumpur'));
    $from = $date1->format('Y-m-d');
    $date2 = new DateTime($to, new DateTimeZone('Asia/Kuala_Lumpur'));
    $to = $date2->format('Y-m-d');

    if ($purpose == 'Create') {
        // $prefixResult = $db->getResultSet('gendoc_mstr', ['gendoc_prefix'], ['gendoc_kk = "' . $fromkk . '"', 'gendoc_active = "Y"']);
        // $prefixRow = $prefixResult->fetch_assoc();
        // $prefix = 'S' . $prefixRow['gendoc_prefix'];

        // $maxIDResult = $db->getResultSet('spubperm_mstr', ['max(spubperm_code) as spubperm_code'], ['spubperm_kk = "' . $fromkk . '"']);
        // $maxIDRow = $maxIDResult->fetch_assoc();
        // $maxID = $maxIDRow['spubperm_code'];

        // if (empty($maxID)) {
        //     $maxID = '000000';
        // }

        // // Generate patient with own kk prefix
        // $newCodeInt = (int)(substr($maxID, strlen($maxPatCode) - 5) + 1);
        // $format = $prefix . '%1$05d';
        // $newCode = sprintf($format, $newCodeInt);

        $newCode = 'S' . $patcode;

        $insertResult = $db->insertData('spubperm_mstr',
            ['spubperm_code', 'spubperm_patcode', 'spubperm_kk', 'spubperm_from', 
            'spubperm_to', 'spubperm_sunday', 'spubperm_monday', 'spubperm_tuesday', 
            'spubperm_wednesday', 'spubperm_thursday', 'spubperm_friday', 'spubperm_saturday', 
            'spubperm_active', 'user_created', 'date_created'],
            [$newCode, $patcode, $fromkk, $from, 
            $to, $sunday, $monday, $tuesday, 
            $wednesday, $thursday, $friday, $saturday, 
            'Y', $user, $date]);

        $txt = '{';
        $txt .= '"insert":"' . $insertResult . '", ';
        $txt .= '"code":"' . $newCode . '", ';
        $txt .= '"patcode":"' . $patcode . '" ';
        $txt .= '}';
    } else if ($purpose == 'Update') {
        $updateResult = $db->updateData('spubperm_mstr',
            ['spubperm_from = "' . $from . '"', 'spubperm_to = "' . $to . '"',
            'spubperm_active = "' . $active . '"', 'spubperm_sunday = "' . $sunday . '"', 
            'spubperm_monday = "' . $monday . '"', 'spubperm_tuesday = "' . $tuesday . '"', 
            'spubperm_wednesday = "' . $wednesday . '"', 'spubperm_thursday = "' . $thursday . '"', 
            'spubperm_friday = "' . $friday . '"', 'spubperm_saturday = "' . $saturday . '"', 
            'user_updated = "' . $user . '"', 'date_updated = "' . $date . '"'],
            ['spubperm_code = "' . $code . '"']);

        $txt = '{';
        $txt .= '"update":"' . $updateResult . '", ';
        $txt .= '"code":"' . $code . '" ';
        $txt .= '}';
    }

        

    header('Content-Type: application/json');

    echo $txt;
?>
