<?php
    header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
    header("Cache-Control: no-cache");
    header("Pragma: no-cache");
 
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

    $date1 = new Date($timeZone);
    $todayDate = $date1->getMySQLFormat();

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

        /*Get transout_mstr count*/
        $doseResult = $db->getResultSet(['dose_trig a', 'patient_mstr b'], 
            ['dose_date', 'dose_patcode', 'patient_name', 'dose_last', 'dose_new', 'a.user_updated', 'a.date_updated'], 
            ['dose_date >= "' . $fromDate . '"', 'dose_date < "' . $newToDate->getMySQLFormat() . '"',
            'dose_patcode = patient_code'], 
            ['dose_id'], ['date_updated']);
        $doseCount = $doseResult->num_rows;

    }
    
?>

<!DOCTYPE html>
<html>
<head>
    <title>MethaSys-Patient Dose History Report</title>
    <link rel="stylesheet" type="text/css" href="../css/doseChangeStyle.css">
    <script src="../scripts/jquery-1.11.2.min.js"></script>
    <style type="text/css" media="print">
        @page {size: landscape;}
    </style>
</head>
<body>
<div id="header">
    <?php require_once '../includes/titleHeader.php';?>
        <lable>MethaSys - Patient Dose History Report Page</lable>
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
                <input type="date" id="fromDate" name="fromDate" value="<?php echo $todayDate;?>">
                <label> To Date : </label>
                <input type="date" id="toDate" name="toDate" value="<?php echo $todayDate;?>">
                <button id="display">Get</button>
            </p>    
            <p id="register-count"></p>
            <table width="100%" id="table">
                <tr>
                    <th style="width:5%">No.</th>
                    <th style="width:10%">Date</th>
                    <th style="width:10%">Patient Code</th>
                    <th style="width:25%">Patient Name</th>
                    <th style="width:10%">Previous Dose(mg)</th>
                    <th style="width:10%">Latest Dose(mg)</th>
                    <th style="width:10%">User Updated</th>
                    <th style="width:20%">Date Updated</th>
                </tr>
                <?php
                    if ($_SERVER['REQUEST_METHOD'] == 'POST'){
                        $count = 1;
                        $totalDose = 0;
                        $totalVolume = 0;

                        while ($row = $doseResult->fetch_assoc()) {
                            if ($count%2 == 0) {
                                echo '<tr class="even">';
                            } else {
                                echo '<tr>';
                            }  
                            echo '<td style="width:5%">' . $count . '</td>';
                            echo '<td style="width:10%">' . $row['dose_date'] . '</td>';
                            echo '<td style="width:10%">' . $row['dose_patcode'] . '</td>';
                            echo '<td style="width:25%;text-align:left;">' . $row['patient_name'] . '</td>';
                            echo '<td style="width:10%">' . $row['dose_last'] . '</td>';
                            echo '<td style="width:10%">' . $row['dose_new'] . '</td>';
                            echo '<td style="width:10%">' . $row['user_updated'] . '</td>';
                            echo '<td style="width:20%">' . $row['date_updated'] . '</td>';
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
