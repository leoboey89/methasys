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

    $user = $_COOKIE['user'];
    $kk = $_COOKIE['kk'];
    $db = new MySQLConnector('localhost', 'leoboey_db', 'methasys2015', 'leoboey_db');
    $result = $db->getResultSet('annc_hist',['annc_id','annc_title', 'annc_message', 'user_created', 'date_created'], ["annc_kk = '$kk'"], ['annc_id'], ['date_created desc']);
    
?>

<!DOCTYPE html>
<html>
<head>
    <title>MethaSys-Annoucement</title>
    <link rel="stylesheet" type="text/css" href="css/anncStyle.css">
    <style type="text/css" media="print">
        @page {size: landscape;}
    </style>
</head>
<body>
    <div id="header">
        <?php require_once 'includes/titleHeader.php';?>
            <label>MethaSys - Annoucement Page</label>
        <?php require_once 'includes/titleFooter.php';?>
        <?php require_once 'includes/menuHeader.php';?>
                <li><a href="announcement.php" style="color: #FFFFFF;font-size: 18px;text-decoration: none;">Annoucement</a></li>
                <li><a href="scan.php">Scan</a></li>
                <li><a href="home.php">Log</a></li>
                <li><a href="spubm1m.php">SPUB/M1M</a></li>
                <li><a href="maintenance.php">Maintenance</a></li>
                <li><a href="report.php">Report</a></li>
        <?php require_once 'includes/menuFooter.php';?>
    </div>

    <div id="content">
        <?php
             while ($row = $result->fetch_assoc()){
                $dateObj = new DateTime($row['date_created'], new DateTimeZone('Asia/Kuala_Lumpur'));
                $date = $dateObj->format('l jS \of F Y h:i:s A');
                echo '<p id="title"><a href="maintenance/annc_maintenance.php?id=' . urlencode($row['annc_id']) . '&user=' . $user . '">' . $row['annc_title'] . '</a></p>';
                echo '<p id="author">Posted By : ' . $row['user_created'] . '</p>';
                echo '<p id="date">' . $date . '</p>';
                echo '<p id="message">' . nl2br($row['annc_message']) . '</p>';
                echo '<hr>';
             }
        ?>
        
    </div>     
    <?php require_once 'includes/pageFooter.php';?>
</body>
</html>
