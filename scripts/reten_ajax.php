<?php

    require_once '../classes/MySQLConnector.php';
    require_once '../classes/Date.php';

    $db = new MySQLConnector('localhost', 'leoboey_db', 'methasys2015', 'leoboey_db');

    /*Get fromDate from javascript and santify it before used in database*/
    $fromDate = trim($_REQUEST['fromDate']);
    $fromDate = strtoupper($db->realEscapeString($fromDate));

    /*Get toDate from javascript and santify it before used in database*/
    $toDate = trim($_REQUEST['toDate']);
    $toDate = strtoupper($db->realEscapeString($toDate));

    /*Get register_mstr count*/
    $registeredResult = $db->getResultSet('register_mstr', ['*'], 
        ['date_created >= "' . $fromDate . '"', 'date_created < "' . $toDate . '"', 'register_active="Y"']);
    $registerCount = $registeredResult->num_rows;

    /*Get lost_mstr count*/
    $lostResult = $db->getResultSet('lost_mstr', ['*'], 
        ['date_created >= "' . $fromDate . '"', 'date_created < "' . $toDate . '"', 'lost_active="Y"']);
    $lostCount = $lostResult->num_rows;

    /*Get death_mstr count*/
    $deathResult = $db->getResultSet('death_mstr', ['*'], 
        ['date_created >= "' . $fromDate . '"', 'date_created < "' . $toDate . '"', 'death_active="Y"']);
    $deathCount = $deathResult->num_rows;

    /*Get reactivate_mstr count*/
    $reactivateResult = $db->getResultSet('reactivate_mstr', ['*'], 
        ['date_created >= "' . $fromDate . '"', 'date_created < "' . $toDate . '"', 'reactivate_active="Y"']);
    $reactivateCount = $reactivateResult->num_rows;

    /*Get transin_mstr count*/
    $transinResult = $db->getResultSet('transin_mstr', ['*'], 
        ['date_created >= "' . $fromDate . '"', 'date_created < "' . $toDate . '"', 'transin_active="Y"']);
    $transinCount = $transinResult->num_rows;

    /*Get transout_mstr count*/
    $transoutResult = $db->getResultSet('transout_mstr', ['*'], 
        ['date_created >= "' . $fromDate . '"', 'date_created < "' . $toDate . '"', 'transout_active="Y"']);
    $transoutCount = $transoutResult->num_rows;

    /*Get terminated_mstr count*/
    $terminatedResult = $db->getResultSet('terminated_mstr', ['*'], 
        ['date_created >= "' . $fromDate . '"', 'date_created < "' . $toDate . '"', 'terminated_active="Y"']);
    $terminatedCount = $terminatedResult->num_rows;

    $overallCount = ($registerCount + $transinCount) - ($deathCount + $transoutCount + $terminatedCount);
    $activeCount = ($registerCount + $reactivateCount + $transinCount) - ($lostCount + $deathCount + $transoutCount + $terminatedCount);
    $retentionRate = number_format(($activeCount/(($overallCount + $transinCount) - ($deathCount + $transoutCount + $terminatedCount))) * 100, 2, '.', '');
    /*$retentionRate = ($activeCount/(($overallCount + $transinCount) - ($deathCount + $transoutCount + $terminatedCount)));*/

    header('Content-Type: application/json');

    $txt = '{';
    $txt .= '"register_count":"' . $registerCount . '", ';
    $txt .= '"overall_count":"' . $overallCount . '", ';
    $txt .= '"lost_count":"' . $lostCount . '", ';
    $txt .= '"death_count":"' . $deathCount . '", ';
    $txt .= '"reactivate_count":"' . $reactivateCount . '", ';
    $txt .= '"transin_count":"' . $transinCount . '", ';
    $txt .= '"transout_count":"' . $transoutCount . '", ';
    $txt .= '"terminated_count":"' . $terminatedCount . '", ';
    $txt .= '"active_count":"' . $activeCount . '", ';
    $txt .= '"retention_rate":"' . $retentionRate . '" ';
    $txt .= '}';

    echo $txt;
?>
