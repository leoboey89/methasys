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

        /*Get transin_mstr count*/
        $suspendedResult = $db->getResultSet('suspended_mstr', ['suspended_patcode', 'suspended_patname', 'date_created'], 
            ['date_created >= "' . $fromDate . '"', 'date_created < "' . $newToDate->getMySQLFormat() . '"', 
            'suspended_active="Y"', "suspended_kk = '$kk'"], ['suspended_id'], ['suspended_id']);
        $suspendedCount = $suspendedResult->num_rows;

    }
    
?>

<!DOCTYPE html>
<html>
<head>
    <title>MethaSys-Suspended Report</title>
    <link rel="stylesheet" type="text/css" href="../css/commonStyle.css">
    <script src="../scripts/jquery-1.11.2.min.js"></script>
    <style type="text/css" media="print">
        @page {size: landscape;}
    </style>
</head>
<body>
<div id="header">
    <?php require_once '../includes/titleHeader.php';?>
        <lable>MethaSys - Suspended Report Page</lable>
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
            <table width="100%" id="table">
                <tr>
                    <th style="width:5%">No.</th>
                    <th style="width:15%">Patient code</th>
                    <th style="width:45%">Patient Name</th>
                    <th style="width:35%">Time Created</th>
                </tr>
                <?php
                    if ($_SERVER['REQUEST_METHOD'] == 'POST'){
                        $count = 1;

                        while ($row = $suspendedResult->fetch_assoc()) {
                            if ($count%2 == 0) {
                                echo '<tr class="even">';
                            } else {
                                echo '<tr>';
                            }  
                            echo '<td style="width:5%">' . $count . '</td>';
                            echo '<td style="width:15%">' . $row['suspended_patcode'] . '</td>';
                            echo '<td style="width:45%;text-align:left;">' . $row['suspended_patname'] . '</td>';
                            echo '<td style="width:35%">' . $row['date_created'] . '</td>';
                            echo '</tr>';
                            $count++;
                        }
                    }
                ?>
            </table>
        </form>
             
    </div>     
    <?php require_once '../includes/pageSubFooter.php';?>
</body>
</html>
