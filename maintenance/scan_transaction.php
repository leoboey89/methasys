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

    $db = new MySQLConnector('localhost', 'leoboey_db', 'methasys2015', 'leoboey_db');
    $kk = $_COOKIE['kk'];
    $user = $_COOKIE['user'];

    if ($_SERVER['REQUEST_METHOD'] == 'POST'){


        if(!empty($_POST['delete'])) {
            if(!empty($_POST['methascan_id'])) {
                $values = $_POST['methascan_id'];
                foreach ($values as $row) {
                    $result = $db->getResultSet('methascan_hist', 
                        ['methascan_patcode','methascan_date','methascan_patstatus'], 
                        ['methascan_id = "' . $row . '"', 'methascan_kk = "' .$kk . '"']);
                    $resultRow = $result->fetch_assoc();
                    $scanDateFrom = new Date($timeZone);
                    $scanDateFrom->setFromMySQL($resultRow['methascan_date']);
                    $scanDateTo = new Date($timeZone);
                    $scanDateTo->setFromMySQL($resultRow['methascan_date']);
                    $scanDateTo->addDays(1);
                    $from = $scanDateFrom->getMySQLFormat();
                    $to = $scanDateTo->getMySQLFormat();

                    // if ($resultRow['methascan_patstatus'] == 'SPUB IN') {
                    //     date_default_timezone_set('Asia/Kuala_Lumpur');
                    //     $date = date("Y-m-d H:i:s");
                    //     $updateStatus = $db->updateData('spubin_mstr',['spubin_active = "N"', 'user_updated = "' . $_COOKIE['user'] . '"', 'date_updated = "' . $date . '"'], 
                    //         ['date_created >= "' . $from . '"', 'date_created < "' . $to . '"', 
                    //         'spubin_patcode = "' . $resultRow['methascan_patcode'] . '"', 'spubin_kk = "' . $kk . '"']);
                    // } else if ($resultRow['methascan_patstatus'] == 'M1M IN') {
                    //     $updateStatus = $db->updateData('m1min_mstr',['m1min_active = "N"', 'user_updated = "' . $_COOKIE['user'] . '"', 'date_updated = "' . $date . '"'], 
                    //         ['date_created >= "' . $from . '"', 'date_created < "' . $to . '"', 
                    //         'm1min_patcode = "' . $resultRow['methascan_patcode'] . '"', 'm1min_kk = "' . $kk . '"']);
                    // } 

                    $db->updateData('dot_mstr', ['dot_active = "N"'],
                        ['dot_patcode = "' . $resultRow['methascan_patcode'] . '"', 'date_created >= "' . $from . '"', 
                        'date_created < "' . $to . '"']);

                    $db->updateData('dbb_mstr', ['dbb_active = "N"'],
                        ['dbb_patcode = "' . $resultRow['methascan_patcode'] . '"', 'date_created >= "' . $from . '"', 
                        'date_created < "' . $to . '"']);

                    $db->updateData('methascan_hist', ['methascan_status = "N"', 'user_updated = "' . $_COOKIE['user'] . '"', 'date_updated = "' . $date . '"'], ["methascan_id = '$row'", "methascan_kk = '$kk'"]);
                }
                echo '<script>alert("Selected records deleted!")</script>';
            } else {
                echo '<script>alert("Please select record before press delete button.")</script>';
            }

        }

        $fromDate = $_POST['fromDate'];
        $toDate = $_POST['toDate'];
        $newToDate = new Date($timeZone);
        $newToDate->setFromMySQL($toDate);
        $newToDate->addDays(1);
        
        $firstOfMonth = $fromDate;
        $lastOfMonth = $toDate;

        $newToDate->getMySQLFormat();

        $methascanResult = $db->getResultSet('methascan_hist', ['methascan_id', 'methascan_patcode', 
            'methascan_patname', 'methascan_dose', 'methascan_volume', 'methascan_date',
            'methascan_methatype', 'methascan_patstatus'], 
            ['methascan_date >= "' . $fromDate . '"', 'methascan_date < "' . $newToDate->getMySQLFormat() . '"', 
            'methascan_status="Y"', 'methascan_kk = "' . $kk . '"'], 
            ['methascan_id'], ['methascan_id']);
        $methascanCount = $methascanResult->num_rows;


    }
    
?>

<!DOCTYPE html>
<html>
<head>
    <title>MethaSys-Scan Transaction</title>
    <link rel="stylesheet" type="text/css" href="../css/transactionStyle.css">
    <script src="../scripts/jquery-1.11.2.min.js"></script>
    <style type="text/css" media="print">
        @page {size: landscape;}
    </style>
</head>
<body>
<div id="header">
    <?php require_once '../includes/titleHeader.php';?>
        <lable>MethaSys - Scan Transaction Page</lable>
    <?php require_once '../includes/titleSubFooter.php';?>
    <?php require_once '../includes/menuHeader.php';?>
        <li><a href="../announcement.php">Annoucement</a></li>
        <li><a href="../scan.php">Scan</a></li>
        <li><a href="../home.php">Log</a></li>
        <li><a href="../spubm1m.php">SPUB/M1M</a></li>
        <li><a href="../maintenance.php" style="color: #FFFFFF;font-size: 18px;text-decoration: none;">Maintenance</a></li>
        <li><a href="../report.php">Report</a></li>
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
            <div style="text-align: center;visibility:<?php
                if (($_SERVER['REQUEST_METHOD'] == 'POST') && ($methascanCount > 0)) {
                    echo "visible";
                } else {
                    echo "hidden";
                }
                    ?>">
                <table id="table">
                    <tr>
                        <th style="width:2%"></th>                    
                        <th style="width:5%">No.</th>
                        <th style="width:10%">Patient code</th>
                        <th style="width:35%">Patient Name</th>
                        <th style="width:5%">Dose</th>
                        <th style="width:5%">Volume</th>
                        <th style="width:10%">Status</th>
                        <th style="width:13%">Date</th>
                    </tr>
                    <?php
                        if ($_SERVER['REQUEST_METHOD'] == 'POST'){
                            $count = 1;

                            while ($row = $methascanResult->fetch_assoc()) {
                                if ($count%2 == 0) {
                                    echo '<tr class="even">';
                                } else {
                                    echo '<tr>';
                                }  
                                echo '<td style="width:2%"><input type="checkbox" name="methascan_id[' . $count . ']" value="' . $row['methascan_id'] . '"></td>';                                
                                echo '<td style="width:5%">' . $count . '</td>';
                                echo '<td style="width:10%">' . $row['methascan_patcode'] . '</td>';
                                echo '<td style="width:35%;text-align:left;">' . $row['methascan_patname'] . '</td>';
                                echo '<td style="width:5%">' . $row['methascan_dose'] . '</td>';
                                echo '<td style="width:5%">' . $row['methascan_volume'] . '</td>';
                                echo '<td style="width:10%">' . $row['methascan_patstatus'] . '</td>';
                                echo '<td style="width:13%">' . $row['methascan_date'] . '</td>';
                                echo '</tr>';
                                $count++;
                            }
                        }
                    ?>
                </table>
                
            </div>
            <div style="text-align: center;visibility:<?php
                if (($_SERVER['REQUEST_METHOD'] == 'POST') && ($methascanCount > 0)) {
                    echo "visible";
                } else {
                    echo "hidden";
                }
                    ?>">
               <input type="submit" id="delete" name="delete" value="Delete">
            </div>
        </form>
             
    </div>     
    <?php require_once '../includes/pageSubFooter.php';?>
</body>
</html>
