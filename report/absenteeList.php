<?php
    require_once '../includes/session.php';
    require_once '../classes/Date.php';
    require_once '../classes/MySQLConnector.php';
    require_once '../includes/session.php';

    //Get day numbder of week
    date_default_timezone_set('Asia/Kuala_Lumpur');

    /*Get today's date*/
    $timeZone = new DateTimeZone('Asia/Kuala_Lumpur');
    $currentDate = new Date($timeZone);
    $fromDate = $currentDate->getMySQLFormat();

    /*Get tomorrow's date*/
    $nextDate = new Date($timeZone);
    $nextDate->addDays(1);
    $toDate = $nextDate->getMySQLFormat();

    $db = new MySQLConnector('localhost', 'leoboey_db', 'methasys2015', 'leoboey_db');
    $kk = $_COOKIE['kk'];

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $fromDate = $_POST['date-selector'];
        $newToDate = new Date($timeZone);
        $newToDate->setFromMySQL($fromDate);
        $newToDate->addDays(1);

        $absenteeResult = $db->getResultBySQL('SELECT * FROM patient_mstr WHERE patient_code IN 
        (SELECT patient_code FROM patient_mstr LEFT JOIN methascan_hist ON methascan_patcode = patient_code AND 
        methascan_date >= "' . $fromDate . '" AND methascan_date < "' . $newToDate->getMySQLFormat() . '" WHERE patient_active = "Y"
        AND patient_status <> "DEATH" AND patient_status <> "TRANSFER OUT" AND patient_status <> "M1M IN" 
        AND patient_kk = "' . $kk . '" AND methascan_patcode IS NULL) ORDER BY patient_code');
    } else {
        $absenteeResult = $db->getResultBySQL('SELECT * FROM patient_mstr WHERE patient_code IN 
        (SELECT patient_code FROM patient_mstr LEFT JOIN methascan_hist ON methascan_patcode = patient_code AND 
        methascan_date >= "' . $fromDate . '" AND methascan_date < "' . $toDate . '" WHERE patient_active = "Y"
        AND patient_status <> "DEATH" AND patient_status <> "TRANSFER OUT" AND patient_status <> "M1M IN" 
        AND patient_kk = "' . $kk . '" AND methascan_patcode IS NULL) ORDER BY patient_code');
    }

?>

<!DOCTYPE html>
<html>
<head>
    <title>MethaSys-Absentee List Report</title>
    <link rel="stylesheet" type="text/css" href="../css/absenteeStyle.css">
    <style type="text/css" media="print">
        @page {size: landscape;}
    </style>
</head>
<body>
<div id="header">
    <?php require_once '../includes/titleHeader.php';?>
        <label>MethaSys - Absentee List Page</label>
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
            <div style="text-align:center;padding-bottom:20px;font-weight:bold;">
                <label>Date: </label>
                <input type="date" id="date-selector" name="date-selector" value="<?php echo $fromDate;?>" oninput="this.form.submit()">  
            </div>
            <div id="absentee-log">
                <table width="100%" id="absentee-table">
                    <tr>
                        <th>No</th>
                        <th>Code</th>
                        <th>Name</th>
                        <th>Status</th>
                    </tr>
                    <?php 
                        $count = 1;

                        while ($row = $absenteeResult->fetch_assoc()){
                            if ($count%2 == 0) {
                                echo '<tr class="even">';
                            } else {
                                echo '<tr>';
                            }                       
                            echo '<td>' . $count . '</td>';
                            echo '<td>' . $row['patient_code'] . '</td>';
                            echo '<td>' . $row['patient_name'] . '</td>';
                            echo '<td>' . $row['patient_status'] . '</td>';
                            $count++;
                        }
                    ?>
                </table>
            </div>    
        </form>
    </div>     
    <?php require_once '../includes/pageSubFooter.php';?>
</body>
</html>


