<?php
    session_set_cookie_params(0);

    require_once '../classes/MySQLConnector.php';
    require_once '../classes/Date.php';
    require_once '../includes/session.php';

    $db = new MySQLConnector('localhost', 'leoboey_db', 'methasys2015', 'leoboey_db');

    $kkResult = $db->getResultSet('kk_mstr', ['kk_code', 'kk_name'], ['kk_active = "Y"', 'kk_hq <> "Y"'], ['kk_id'], ['kk_code']);

    while ($row = $kkResult->fetch_assoc()) {
        $code[] = $row['kk_code'];
        $name[] = $row['kk_name'];
    } 

    $txt = array_combine($code, $name);

    header('Content-Type: application/json');

    echo json_encode($txt);
?>
