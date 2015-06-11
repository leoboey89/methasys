<?php    
    session_set_cookie_params(0);

    require_once '../classes/Date.php';
    require_once '../classes/MySQLConnector.php';
    require_once '../includes/session.php';

    /*Get today's date*/
    $timeZone = new DateTimeZone('Asia/Kuala_Lumpur');
    $currentDate = new Date($timeZone);
    $currentDate->setFirstOfMonth();
    $firstOfMonth = $currentDate->getMySQLFormat();

    $currentDate->setLastOfMonth();
    $lastOfMonth = $currentDate->getMySQLFormat();

    /*Get tomorrow's date*/
    $nextDate = new Date($timeZone);
    $nextDate->addDays(1);
    $tomorrowDate = $nextDate->getMySQLFormat();

    $registerCount = '';
    $overallCount = '';
    $lostCount = '';
    $deathCount = '';
    $reactivateCount = '';
    $transinCount = '';
    $transoutCount = '';
    $terminatedCount = '';
    $activeCount = '';
    $retentionRate = '';
    $dotCount = '';
    $dbbCount = '';
    $spubInCount = '';
    $spubInAmtCount = '';
    $spubOutCount = '';
    $kk = $_COOKIE['kk'];


    if ($_SERVER['REQUEST_METHOD'] == 'POST'){
        $fromDate = $_POST['fromDate'];
        $toDate = $_POST['toDate'];
        
        $firstOfMonth = $fromDate;
        $newToDate = new Date($timeZone);
        $newToDate->setFromMySQL($toDate);
        $newToDate->addDays(1);
        $lastOfMonth = $toDate;

        $db = new MySQLConnector('localhost', 'leoboey_db', 'methasys2015', 'leoboey_db');

        /*Get register_mstr count*/
        $registeredResult = $db->getResultSet('register_mstr', ['*'], 
            ['date_created >= "' . $fromDate . '"', 'date_created < "' . $newToDate->getMySQLFormat() . '"', 'register_active="Y"', "register_kk = '$kk'"]);
        $registerCount = $registeredResult->num_rows;

        /*Get lost_mstr count*/
        $lostResult = $db->getResultSet('lost_mstr', ['*'], 
            ['date_created >= "' . $fromDate . '"', 'date_created < "' . $newToDate->getMySQLFormat() . '"', 'lost_active="Y"', "lost_kk = '$kk'"]);
        $lostCount = $lostResult->num_rows;

        /*Get death_mstr count*/
        $deathResult = $db->getResultSet('death_mstr', ['*'], 
            ['date_created >= "' . $fromDate . '"', 'date_created < "' . $newToDate->getMySQLFormat() . '"', 'death_active="Y"', "death_kk = '$kk'"]);
        $deathCount = $deathResult->num_rows;

        /*Get reactivate_mstr count*/
        $reactivateResult = $db->getResultSet('reactivate_mstr', ['*'], 
            ['date_created >= "' . $fromDate . '"', 'date_created < "' . $newToDate->getMySQLFormat() . '"', 'reactivate_active="Y"', "reactivate_kk = '$kk'"]);
        $reactivateCount = $reactivateResult->num_rows;

        /*Get transin_mstr count*/
        $transinResult = $db->getResultSet('transin_mstr', ['*'], 
            ['date_created >= "' . $fromDate . '"', 'date_created < "' . $newToDate->getMySQLFormat() . '"', 'transin_active="Y"', "transin_kk = '$kk'"]);
        $transinCount = $transinResult->num_rows;

        /*Get transout_mstr count*/
        $transoutResult = $db->getResultSet('transout_mstr', ['*'], 
            ['date_created >= "' . $fromDate . '"', 'date_created < "' . $newToDate->getMySQLFormat() . '"', 'transout_active="Y"', "transout_kk = '$kk'"]);
        $transoutCount = $transoutResult->num_rows;

        /*Get terminated_mstr count*/
        $terminatedResult = $db->getResultSet('terminated_mstr', ['*'], 
            ['date_created >= "' . $fromDate . '"', 'date_created < "' . $newToDate->getMySQLFormat() . '"', 'terminated_active="Y"', "terminated_kk = '$kk'"]);
        $terminatedCount = $terminatedResult->num_rows;

        /*Get dot_mstr count*/
        $dotResult = $db->getResultSet('methascan_hist', ['*'], 
            ['methascan_date >= "' . $fromDate . '"', 'methascan_date < "' . $newToDate->getMySQLFormat() . '"', 'methascan_status="Y"', 'methascan_dot = "Y"', "methascan_kk = '$kk'"]);
        $dotCount = $dotResult->num_rows;

        /*Get dbb_mstr count*/
        $dbbResult = $db->getResultSet('methascan_hist', ['*'], 
            ['methascan_date >= "' . $fromDate . '"', 'methascan_date < "' . $newToDate->getMySQLFormat() . '"', 'methascan_status="Y"', 'methascan_dbb = "Y"', "methascan_kk = '$kk'"]);
        $dbbCount = $dbbResult->num_rows;

        /*Get spubin_mstr count*/
        $spubInResult = $db->getResultSet('spubin_mstr', ['*'], 
            ['date_created >= "' . $fromDate . '"', 'date_created < "' . $newToDate->getMySQLFormat() . '"', 'spubin_active="Y"', "spubin_kk = '$kk'"],
            ['spubin_patcode']);
        $spubInCount = $spubInResult->num_rows;

        /*Get m1min_mstr count*/
        $spubInAmtResult = $db->getResultSet('methascan_hist', ['*'], 
            ['date_created >= "' . $fromDate . '"', 'date_created < "' . $newToDate->getMySQLFormat() . '"', 'methascan_status="Y"', 'methascan_patstatus = "SPUB IN"', "methascan_kk = '$kk'"]);
        $spubInAmtCount = $spubInAmtResult->num_rows;

        /*Get spubout_mstr count*/
        $spubOutResult = $db->getResultSet('spubout_mstr', ['*'], 
            ['date_created >= "' . $fromDate . '"', 'date_created < "' . $newToDate->getMySQLFormat() . '"', 'spubout_active="Y"', "spubout_kk = '$kk'"]);
        $spubOutCount = $spubOutResult->num_rows;


        $overallCount = ($registerCount + $transinCount) - ($deathCount + $transoutCount + $terminatedCount);
        $activeCount = ($registerCount + $reactivateCount + $transinCount) - ($lostCount + $deathCount + $transoutCount + $terminatedCount);
        $retentionRate = @number_format(($activeCount/(($overallCount + $transinCount) - ($deathCount + $transoutCount + $terminatedCount))) * 100, 2, '.', '');

    }
    
?>

<!DOCTYPE html>
<html>
<head>
    <title>MethaSys-Reten Report</title>
    <link rel="stylesheet" type="text/css" href="../css/retenStyle.css">
    <script src="../scripts/jquery-1.11.2.min.js"></script>
    <style type="text/css" media="print">
        @page {size: landscape;}
    </style>
    <script>

/*        var retenWin = window.open('','retenWindow','fullscreen=yes');
        retenWin.document.write('<html><head><title>MethaSys-Log</title><link rel="stylesheet" type="text/css" href="../css/retenStyle.css"><style type="text/css" media="print">@page {size: landscape;}</style></head><body>');
        retenWin.document.write($("#content").html());
        retenWin.document.write('</body></html>');
        retenWin.print();*/
    </script>
</head>
<body>
<div id="header">
    <?php require_once '../includes/titleHeader.php';?>
        <lable>MethaSys - Reten Report Page</lable>
    <?php require_once '../includes/titleSubFooter.php';?>
    <?php require_once '../includes/menuHeader.php';?>
        <li><a href="../announcement.php">Annoucement</a></li>
        <li><a href="../scan.php">Scan</a></li>
        <li><a href="../home.php">Log</a></li>
        <li><a href="../spubm1m.php">SPUB/M1M</a></li>
        <li><a href="../maintenance.php">Maintenance</a></li>
        <li><a href="../report.php" style="color: #FFFFFF;font-size: 18px;text-decoration: none;">Report</a></li>
    <?php require_once '../includes/menuFooter.php';?>
</div>

    <div id="content">
        <form method="post">
            <p style="font-weight:bold;padding-left:20px;">
                <label>From Date : </label>
                <input type="date" id="fromDate" name="fromDate" value="<?php echo $firstOfMonth;?>">
                <label> To Date : </label>
                <input type="date" id="toDate" name="toDate" value="<?php echo $lastOfMonth;?>">
                <button id="display">Get</button>
            </p>    
            <p id="register-count"></p>
            <table width="100%" id="reten-table">
                <tr>
                    <th colspan="12" rowspan="2">Patient Amount</th>
                    <th colspan="3">SPUB</th>
                </tr>
                <tr>
                    <th colspan="2">SPUB In</th>
                    <th>SPUB Out</th>
                </tr>
                <tr>
                    <th>Register(I)</th>
                    <th>Overall(B)</th>
                    <th>Lost(J)</th>
                    <th>Death(K)</th>
                    <th>Reactivate(L)</th>
                    <th>Transfer In(M)</th>
                    <th>Transfer Out(N)</th>
                    <th>Terminated(O)</th>
                    <th>Active(P)</th>
                    <th>Retention Rate(%)</th>
                    <th>DOT</th>
                    <th>DBB</th>
                    <th>Patient Amt</th>
                    <th>Dose Amt</th>
                    <th>Patient Amt</th>
                </tr>
                <?php
                    echo '<tr>';
                    echo '<td>' . $registerCount . '</td>';
                    echo '<td>' . $overallCount . '</td>';
                    echo '<td>' . $lostCount . '</td>';
                    echo '<td>' . $deathCount . '</td>';
                    echo '<td>' . $reactivateCount . '</td>';
                    echo '<td>' . $transinCount . '</td>';
                    echo '<td>' . $transoutCount . '</td>';
                    echo '<td>' . $terminatedCount . '</td>';
                    echo '<td>' . $activeCount . '</td>';
                    echo '<td>' . $retentionRate . '</td>';
                    echo '<td>' . $dotCount . '</td>';
                    echo '<td>' . $dbbCount . '</td>';
                    echo '<td>' . $spubInCount . '</td>';
                    echo '<td>' . $spubInAmtCount . '</td>';
                    echo '<td>' . $spubOutCount . '</td>';
                    echo '</tr>';
                ?>
            </table>
        </form>
             
    </div>     
    <?php require_once '../includes/pageSubFooter.php';?>
</body>
</html>
