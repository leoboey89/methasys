<?php
    session_set_cookie_params(0);

    require_once '../classes/MySQLConnector.php';
    require_once '../classes/Date.php';
    require_once '../includes/session.php';

    $db = new MySQLConnector('localhost', 'leoboey_db', 'methasys2015', 'leoboey_db');

    $spubPermCode = $_REQUEST['spubPermCode'];

    $spubPermResult = $db->getResultSet(['spubperm_mstr', 'kk_mstr'], 
        ["kk_name", "spubperm_patcode", "spubperm_active", "spubperm_from",
        "spubperm_to", "spubperm_sunday", "spubperm_monday", "spubperm_tuesday", 
        "spubperm_wednesday", "spubperm_thursday", "spubperm_friday", "spubperm_saturday"], 
        ["spubperm_code = '$spubPermCode'", "spubperm_kk = kk_code"]);

    $row = $spubPermResult->fetch_assoc();
    $fromDateObj = new DateTime($row['spubperm_from'], new DateTimeZone('Asia/Kuala_Lumpur'));
    $toDateObj = new DateTime($row['spubperm_to'], new DateTimeZone('Asia/Kuala_Lumpur'));

    header('Content-Type: application/json');

    $txt = '{';
    $txt .= '"kk":"' . $row['kk_name'] . '", ';
    $txt .= '"patcode":"' . $row['spubperm_patcode'] . '", ';
    $txt .= '"active":"' . $row['spubperm_active'] . '", ';
    $txt .= '"from":"' . $fromDateObj->format('Y-m-d') . '", ';
    $txt .= '"to":"' . $toDateObj->format('Y-m-d') . '", ';
    $txt .= '"sunday":"' . $row['spubperm_sunday'] . '", ';
    $txt .= '"monday":"' . $row['spubperm_monday'] . '", ';
    $txt .= '"tuesday":"' . $row['spubperm_tuesday'] . '", ';
    $txt .= '"wednesday":"' . $row['spubperm_wednesday'] . '", ';
    $txt .= '"thursday":"' . $row['spubperm_thursday'] . '", ';
    $txt .= '"friday":"' . $row['spubperm_friday'] . '", ';
    $txt .= '"saturday":"' . $row['spubperm_saturday'] . '" ';
    $txt .= '}';

    echo $txt;
?>
