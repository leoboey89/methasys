<?php
    header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
    header("Cache-Control: no-cache");
    header("Pragma: no-cache");

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

    $kk = $_COOKIE['kk'];
    $user = $_COOKIE['user'];

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

    if ($_SERVER['REQUEST_METHOD'] == 'POST'){
        $fromDate = $_POST['fromDate'];
        $toDate = $_POST['toDate'];
        
        $firstOfMonth = $fromDate;
        $newToDate = new Date($timeZone);
        $newToDate->setFromMySQL($toDate);
        $newToDate->addDays(1);
        $lastOfMonth = $toDate;
        $newDate = $newToDate->getMySQLFormat();

        $date1 = new DateTime($fromDate);
        $date1->modify('-1 day');
        $yestDate = $date1->format('Y-m-d');

        $db = new MySQLConnector('localhost', 'leoboey_db', 'methasys2015', 'leoboey_db');

        $db->getResultBySQL('DELETE FROM temp_stock_hist');

        // Opening stock info
        $totalStockInVolumeResult = $db->getResultSet('stockin_hist', ['stockin_date', 'sum(stockin_volume) as stockin_volume'], 
            ["stockin_date < '$fromDate'", 'stockin_active = "Y"', "stockin_kk = '$kk'"]);
        $totalStockOutVolumeResult = $db->getResultSet('stockout_hist', ['stockout_date', 'sum(stockout_volume) as stockout_volume'], 
            ["stockout_date < '$fromDate'", 'stockout_active = "Y"', "stockout_kk = '$kk'"]);
        $totalStockBalVolumeResult = $db->getResultSet('bbal_hist', ['bbal_date', 'sum(bbal_volume) as bbal_volume'], 
            ["bbal_date < '$fromDate'", 'bbal_active = "Y"', "bbal_kk = '$kk'"]);
        $totalUsageResult = $db->getResultSet('methascan_hist', ['methascan_date', 'sum(methascan_volume) as methascan_volume'], 
            ["methascan_date < '$fromDate'", 'methascan_status = "Y"', "methascan_kk = '$kk'"]);

        if ($stockInRow = $totalStockInVolumeResult->fetch_assoc()) {
            $tempStockResult = $db->insertData('temp_stock_hist', ['stock_date', 'stock_in'], [
                $yestDate, $stockInRow['stockin_volume']]);
        } 

        if ($stockOutRow = $totalStockOutVolumeResult->fetch_assoc()) {
            $tempStockResult = $db->updateData('temp_stock_hist', 
                ['stock_out = "' . $stockOutRow['stockout_volume'] . '"'], 
                ['stock_date = "' . $yestDate . '"']);
        } 

        if ($stockBalRow = $totalStockBalVolumeResult->fetch_assoc()) {
            $tempStockResult = $db->updateData('temp_stock_hist', 
                ['stock_balance = "' . $stockBalRow['bbal_volume'] . '"'], 
                ['stock_date = "' . $yestDate . '"']);
        } 

        if ($stockUsageRow = $totalUsageResult->fetch_assoc()) {
            $tempStockResult = $db->updateData('temp_stock_hist', 
                ['stock_usage = "' . $stockUsageRow['methascan_volume'] . '"'], 
                ['stock_date = "' . $yestDate . '"']);
        } 

        while ($fromDate < $newDate) {
            $countDate = new DateTime($fromDate);
            $countDate->modify('+1 day');
            $newNextDate = $countDate->format('Y-m-d');

            /*Get total volume*/
            $totalStockInVolumeResult = $db->getResultSet('stockin_hist', ['stockin_date', 'sum(stockin_volume) as stockin_volume'], 
                ["stockin_date >= '$fromDate'", "stockin_date < '$newNextDate'", 'stockin_active = "Y"', "stockin_kk = '$kk'"], 
                ['CAST(stockin_date AS DATE)']);
            $totalStockOutVolumeResult = $db->getResultSet('stockout_hist', ['stockout_date', 'sum(stockout_volume) as stockout_volume'], 
                ["stockout_date >= '$fromDate'", "stockout_date < '$newNextDate'", 'stockout_active = "Y"', "stockout_kk = '$kk'"], 
                ['CAST(stockout_date AS DATE)']);
            $totalStockBalVolumeResult = $db->getResultSet('bbal_hist', ['bbal_date', 'sum(bbal_volume) as bbal_volume'], 
                ["bbal_date >= '$fromDate'", "bbal_date < '$newNextDate'", 'bbal_active = "Y"', "bbal_kk = '$kk'"], 
                ['CAST(bbal_date AS DATE)']);
            $totalUsageResult = $db->getResultSet('methascan_hist', ['methascan_date', 'sum(methascan_volume) as methascan_volume'], 
                ["methascan_date >= '$fromDate'", "methascan_date < '$newNextDate'", 'methascan_status = "Y"', "methascan_kk = '$kk'"],
                ['CAST(methascan_date AS DATE)']);
            $totalNextStockBalVolumeResult = $db->getResultSet('bbal_hist', ['bbal_date', 'sum(bbal_volume) as bbal_volume'], 
                ["bbal_date = '$newNextDate'", 'bbal_active = "Y"', "bbal_kk = '$kk'"]);

            
            if ($stockInRow = $totalStockInVolumeResult->fetch_assoc()) {
                $tempStockResult = $db->insertData('temp_stock_hist', 
                    ['stock_date', 'stock_in'], [
                    $fromDate, $stockInRow['stockin_volume']]);
            } else {
                $tempStockResult = $db->insertData('temp_stock_hist', 
                    ['stock_date', 'stock_in'], [
                    $fromDate, 0]);
            }

            if ($stockOutRow = $totalStockOutVolumeResult->fetch_assoc()) {
                $tempStockResult = $db->updateData('temp_stock_hist', 
                    ['stock_out = "' . $stockOutRow['stockout_volume'] . '"'], 
                    ['stock_date = "' . subStr($stockOutRow['stockout_date'], 0, 10) . '"']);
            } else {
                $tempStockResult = $db->updateData('temp_stock_hist', 
                    ['stock_out = "0"'], 
                    ['stock_date = "' . $fromDate . '"']);
            }

            if ($stockBalRow = $totalStockBalVolumeResult->fetch_assoc()) {
                $tempStockResult = $db->updateData('temp_stock_hist', 
                    ['stock_balance = "' . $stockBalRow['bbal_volume'] . '"'], 
                    ['stock_date = "' . subStr($stockBalRow['bbal_date'], 0, 10) . '"']);
            } else {
                $tempStockResult = $db->updateData('temp_stock_hist', 
                    ['stock_balance = "0"'], 
                    ['stock_date = "' . $fromDate . '"']);
            }

            if ($stockUsageRow = $totalUsageResult->fetch_assoc()) {
                $tempStockResult = $db->updateData('temp_stock_hist', 
                    ['stock_usage = "' . $stockUsageRow['methascan_volume'] . '"'], 
                    ['stock_date = "' . subStr($stockUsageRow['methascan_date'], 0, 10) . '"']);
            } else {
                $tempStockResult = $db->updateData('temp_stock_hist', 
                    ['stock_usage = "0"'], 
                    ['stock_date = "' . $fromDate . '"']);
            }

            if ($nextStockBalRow = $totalNextStockBalVolumeResult->fetch_assoc()) {
                $tempStockResult = $db->updateData('temp_stock_hist', 
                    ['stock_spillage = "' . $nextStockBalRow['bbal_volume'] . '" - ((stock_out + stock_balance) - stock_usage)'], 
                    ['stock_date = "' . subStr($stockUsageRow['methascan_date'], 0, 10) . '"']);
            } else {
                $tempStockResult = $db->updateData('temp_stock_hist', 
                    ['stock_spillage = "0"'], 
                    ['stock_date = "' . $fromDate . '"']);
            }

            $fromDate = $countDate->format('Y-m-d');
        }
       

        $overallResult = $db->getResultSet('temp_stock_hist', 
            ['stock_date', 'stock_in', 'stock_out', 'stock_balance', 'stock_usage', 'stock_spillage']);


    }
    
?>

<!DOCTYPE html>
<html>
<head>
    <title>MethaSys-Stock Report</title>
    <link rel="stylesheet" type="text/css" href="../css/stockRepStyle.css">
    <script src="../scripts/jquery-1.11.2.min.js"></script>
    <style type="text/css" media="print">
        @page {size: landscape;}
    </style>
</head>
<body>
<div id="header">
    <?php require_once '../includes/titleHeader.php';?>
        <lable>MethaSys - Stock Inventory Report Page</lable>
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
                    <th style="width:15%">Stock Date</th>
                    <th style="width:10%">Stock In Volume(ml)</th>
                    <th style="width:10%">Stock Out Volume(ml)</th>
                    <th style="width:10%">Reading Balance Volume(ml)</th>
                    <th style="width:10%">Bottle Volume(ml)</th>
                    <th style="width:10%">Usage Volume(ml)</th>
                    <th style="width:10%">Actual Balance Volume(ml)</th>
                    <th style="width:10%">Spillage/Extra Volume(ml)</th>               
                </tr>
                <?php
                    if ($_SERVER['REQUEST_METHOD'] == 'POST'){
                        $count = 1;
                        $totalStockIn = 0;
                        $totalStockOut = 0;
                        $totalBalance = 0;
                        $totalBottle = 0;
                        $totalUsage = 0;
                        $totalSpillage = 0;              

                        while ($row = $overallResult->fetch_assoc()) {
                            if ($count%2 == 0) {
                                echo '<tr class="even">';
                            } else {
                                echo '<tr>';
                            }  
                            echo '<td style="width:5%">' . $count . '</td>';
                            echo '<td style="width:5%">' . subStr($row['stock_date'], 0, 10) . '</td>';
                            echo '<td style="width:10%">' . $row['stock_in'] . '</td>';
                            echo '<td style="width:10%">' . $row['stock_out'] . '</td>';
                            echo '<td style="width:10%">' . $row['stock_balance'] . '</td>';
                            echo '<td style="width:10%">' . ($row['stock_out'] + $row['stock_balance']) . '</td>';
                            echo '<td style="width:10%">' . $row['stock_usage'] . '</td>';
                            echo '<td style="width:10%">' . (($row['stock_out'] + $row['stock_balance']) - $row['stock_usage']) . '</td>';
                            echo '<td style="width:10%">' . $row['stock_spillage'] . '</td>';
                            echo '</tr>';
                            $count++;
                            $totalStockIn += $row['stock_in'];
                            $totalStockOut += $row['stock_out'];
                            $totalBalance += $row['stock_balance'];
                            $totalBottle += ($row['stock_out'] + $row['stock_balance']);
                            $totalUsage += $row['stock_usage'];
                            $totalSpillage += $row['stock_spillage'];    
                        }
                        echo '<tr id="table-footer">';
                        echo '<td colspan="2">Total</td>';
                        echo '<td>' . $totalStockIn . '</td>';
                        echo '<td>' . $totalStockOut . '</td>';
                        echo '<td>' . $totalBalance . '</td>';
                        echo '<td>' . $totalBottle . '</td>';
                        echo '<td>' . $totalUsage . '</td>';
                        echo '<td>' . ($totalStockOut + $totalBalance - $totalUsage) . '</td>';
                        echo '<td>' . $totalSpillage . '</td>';
                    }
                ?>
            </table>
        </form>
             
    </div>     
    <?php require_once '../includes/pageSubFooter.php';?>
</body>
</html>
