<?php
    require_once 'includes/session.php';
    require_once 'classes/Date.php';
    require_once 'classes/MySQLConnector.php';

    /*Get today's date*/
    $timeZone = new DateTimeZone('Asia/Kuala_Lumpur');
    $currentDate = new Date($timeZone);
    $todayDate = $currentDate->getMySQLFormat();

    /*Get tomorrow's date*/
    $nextDate = new Date($timeZone);
    $nextDate->addDays(1);
    $tomorrowDate = $nextDate->getMySQLFormat();
    $kk = $_COOKIE['kk'];

    $db = new MySQLConnector('localhost', 'leoboey_db', 'methasys2015', 'leoboey_db');
    $result = $db->getResultSet('methascan_hist', ['methascan_patcode', 
        'methascan_patname', 'methascan_dose', 'methascan_volume', 'methascan_date',
        'methascan_methatype', 'methascan_patstatus', 'user_created'], 
        ["methascan_date >= '$todayDate'", "methascan_date < '$tomorrowDate'", 'methascan_status = "Y"', 'methascan_kk = "' . $kk . '"'], 
        ['methascan_id'], ['methascan_id']);

?>

<!DOCTYPE html>
<html>
<head>
    <title>MethaSys-Log</title>
    <link rel="stylesheet" type="text/css" href="css/mystyle.css">
    <style type="text/css" media="print">
        @page {size: landscape;}
    </style>
</head>
<body>
<div id="header">
    <?php require_once 'includes/titleHeader.php';?>
        <label>MethaSys - Log Page</label>
    <?php require_once 'includes/titleFooter.php';?>
    <?php require_once 'includes/menuHeader.php';?>
            <li><a href="announcement.php">Annoucement</a></li>
            <li><a href="scan.php">Scan</a></li>
            <li><a href="home.php" style="color: #FFFFFF;font-size: 18px;text-decoration: none;">Log</a></li>
            <li><a href="spubm1m.php">SPUB/M1M</a></li>
            <li><a href="maintenance.php">Maintenance</a></li>
            <li><a href="report.php">Report</a></li>
    <?php require_once 'includes/menuFooter.php';?>
</div>

    <div id="content">
        <div id="patient-log">
            <p style="font-weight:bold;padding-left:20px;">
                Today's Date: <?php echo substr($todayDate, 0, 10);?>
                <button onclick="window.print()">Print</button>
            </p>
            <table width="100%" id="patient-table">
                <tr>
                    <th>No</th>
                    <th>Code</th>
                    <th>Name</th>
                    <th>Dose(mg)</th>
                    <th>Volume(ml)</th>
                    <th>MethaType</th>
                    <th>Status</th>
                    <th>Create By</th>
                    <th>Time</th>
                </tr>
                <?php 
                    $count = 1;
                    $totalDose = 0;
                    $totalVolume = 0;
                    while ($row = $result->fetch_assoc()){
                        if ($count%2 == 0) {
                            echo '<tr class="even">';
                        } else {
                            echo '<tr>';
                        }                       
                        echo '<td>' . $count . '</td>';
                        echo '<td>' . $row['methascan_patcode'] . '</td>';
                        echo '<td>' . $row['methascan_patname'] . '</td>';
                        echo '<td>' . $row['methascan_dose'] . '</td>';
                        echo '<td>' . $row['methascan_volume'] . '</td>';
                        echo '<td>' . $row['methascan_methatype'] . '</td>';
                        echo '<td>' . $row['methascan_patstatus'] . '</td>';
                        echo '<td>' . $row['user_created'] . '</td>';
                        echo '<td>' . $row['methascan_date'] . '</td>';
                        echo '</tr>';
                        $count++;
                        $totalDose += $row['methascan_dose'];
                        $totalVolume += $row['methascan_volume'];
                    }
                    echo '<tr class="patient-total">';
                    echo '<td></td>';
                    echo '<td></td>';
                    echo '<td>Total</td>';
                    echo '<td>' . $totalDose . '</td>';
                    echo '<td>' . $totalVolume . '</td>';
                    echo '<td></td>';
                    echo '<td></td>';
                    echo '<td></td>';
                    echo '<td></td>';
                    echo '</tr>';
                ?>
            </table>    
        </div>    
    </div>     
    <?php require_once 'includes/pageFooter.php';?>
</body>
</html>
