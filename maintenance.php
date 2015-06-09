<?php

    require_once 'classes/Date.php';
    require_once 'classes/MySQLConnector.php';
    require_once 'classes/Validator.php';
    require_once 'includes/session.php';
    
?>

<!DOCTYPE html>
<html>
<head>
    <title>MethaSys-Maintenance</title>
    <link rel="stylesheet" type="text/css" href="css/mystyle.css">
    <link rel="stylesheet" href="css/jquery-ui.css">
    <script type="text/javascript">

    </script>
</head>
<body>
<div id="header">
    <?php require_once 'includes/titleHeader.php';?>
        <label>MethaSys - Maintenance Page</label>
    <?php require_once 'includes/titleFooter.php';?>
    <?php require_once 'includes/menuHeader.php';?>
            <li><a href="announcement.php">Annoucement</a></li>
            <li><a href="scan.php">Scan</a></li>
            <li><a href="home.php">Log</a></li>
            <li><a href="spubm1m.php">SPUB/M1M</a></li>
            <li><a href="maintenance.php" style="color: #FFFFFF;font-size: 18px;text-decoration: none;">Maintenance</a></li>
            <li><a href="report.php">Report</a></li>
    <?php require_once 'includes/menuFooter.php';?>
</div>

    <div id="content" align="center">
        <table id="maintenance-main-table" width="80%" align="center">
            <tr>
                <th>Maintenance</th>
                <th></th>
            </tr>
            <tr>
                <td><li><a href="maintenance/patient_register_transferin.php">New Patient-Register/Transfer In</a></li></td>
                <td><li><a href="maintenance/patient_reactivate.php">Patient-Reactivate(Suspended/Lost/Terminated)</a></li></td>
            </tr>
            <tr>
                <td><li><a href="maintenance/patient_maintenance.php">Patient-Update/Death/Transfer Out</a></li></td>
                <td><li><a href="maintenance/user_maintenance.php">User Maintenance</a></li></td>
            </tr>
            <tr>
                <td><li><a href="maintenance/stockin_maintenance.php">Stock In Maintenance</a></li></td>
                <td><li><a href="maintenance/stockout_maintenance.php">Stock Out Transaction</a></li></td>
            </tr>
            <tr>
                <td><li><a href="maintenance/stockbal_maintenance.php">Stock Balance Maintenance</a></li></td>
                <td></td>
            </tr>
            <tr>
                <th>Transaction</th>
                <th></th>
            </tr>
            <tr>
                <td><li><a href="maintenance/scan_transaction.php">Scan Transaction</a></li></td>
            </tr>
            <tr>
                <th>Annoucement</th>
                <th></th>
            </tr>
            <tr>
                <td><li><a href="maintenance/annc_maintenance.php">Annoucement</a></li></td>
            </tr>
            <tr>
                <th>Upload</th>
                <th></th>
            </tr>
            <tr>
                <td><li><a href="maintenance/upload_photo.php">Upload Photo</a></li></td>
            </tr>
        </table>
    </div>     
    <?php require_once 'includes/pageFooter.php';?>
</body>
</html>
