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

    $result = true;
    $fromDate = $_REQUEST['from'];
    $toDate = $_REQUEST['to'];
    $patcode = $_REQUEST['patcode'];
    $patname = $_REQUEST['patname'];
    $dose = $_REQUEST['dose'];
    $volume = $_REQUEST['volume'];
    $methatype = $_REQUEST['methatype'];
    $patstatus = $_REQUEST['patstatus'];
    $createBy = $_COOKIE['user'];
    $kk = $_COOKIE['kk'];

    /*Get today's date*/
    $currentDate = new Date($timeZone);
    $todayDate = $currentDate->getMySQLFormat();
    $fromDateObj = new DateTime($fromDate, new DateTimeZone('Asia/Kuala_Lumpur'));
    $toDateObj = new DateTime($toDate, new DateTimeZone('Asia/Kuala_Lumpur'));
    $toDateObjPlusOne = new DateTime($toDate, new DateTimeZone('Asia/Kuala_Lumpur'));
    $toDateObjPlusOne->modify('+1 day');
    $fromDateObjDate = $fromDateObj->format('Y-m-d');
    $toDateObjDate = $toDateObjPlusOne->format('Y-m-d');

    $dbbExist = $db->isExist('methascan_hist', ["methascan_patcode = '$patcode'", "methascan_dbb = 'Y'", "methascan_date >= '$fromDateObjDate'", "methascan_date < '$toDateObjDate'", "methascan_kk = '$kk'"]);

    $dotExist = $db->isExist('methascan_hist', ["methascan_patcode = '$patcode'", "methascan_dot = 'Y'", "methascan_date >= '$fromDateObjDate'", "methascan_date < '$toDateObjDate'", "methascan_kk = '$kk'"]);

    if ($dbbExist || $dotExist) {
        $result = false;
    } else {
        // echo $fromDateObj->format('Y-m-d');
        while ($fromDateObj <= $toDateObj) {
            $newDate = $fromDateObj->format('Y-m-d');

            /*Insert DBB into methascan_hist*/
            $methascanResult = $db->insertData('methascan_hist',['methascan_patcode', 'methascan_patname', 
                        'methascan_dose', 'methascan_volume', 'methascan_date', 'methascan_methatype',
                        'methascan_patstatus', 'methascan_dbb', 'methascan_kk', 'user_created', 
                        'date_created'],
                        [$patcode, $patname,
                        $dose, $volume, $newDate, $methatype, 
                        $patstatus, 'Y', $kk, $createBy, 
                        $newDate]);
            /*Insert DBB into dbb_mstr*/
            $dbbResult = $db->insertData('dbb_mstr',['dbb_patcode', 'dbb_patname', 
                        'dbb_active', 'dbb_dose', 'dbb_volume', 'dbb_kk', 
                        'user_created', 'date_created'],[$patcode, $patname,
                        'Y', $dose, $volume, $kk, 
                        $createBy, $newDate]);
             $fromDateObj->modify("+1 day");

            if (!$methascanResult || !$dbbResult) {
                $result = false;
            }
        }
    }

    header('Content-Type: application/json');

    $txt = '{';
    $txt .= '"insert":"' . $result . '", ';
    $txt .= '"patcode":"' . $patcode . '", ';
    $txt .= '"fromDate":"' . $fromDate . '", ';
    $txt .= '"toDate":"' . $toDate . '", ';
    $txt .= '"dbbExist":"' . $dbbExist . '", ';
    $txt .= '"dotExist":"' . $dotExist . '" ';
    $txt .= '}';

    echo $txt;
?>
