<?php
    require_once 'includes/session.php';
    require_once 'classes/Date.php';
    require_once 'classes/MySQLConnector.php';
    require_once 'classes/Validator.php';
?>

<!DOCTYPE html>
<html>
<head>
    <title>MethaSys-SPUB/M1M</title>
    <link rel="stylesheet" type="text/css" href="css/mystyle.css">
    <link rel="stylesheet" href="css/jquery-ui.css">
    <script type="text/javascript">

    </script>
</head>
<body>
<div id="header">
    <?php require_once 'includes/titleHeader.php';?>
        <label>MethaSys - SPUB/M1M Page</label>
    <?php require_once 'includes/titleFooter.php';?>
    <?php require_once 'includes/menuHeader.php';?>
            <li><a href="announcement.php">Annoucement</a></li>
            <li><a href="scan.php">Scan</a></li>
            <li><a href="home.php">Log</a></li>
            <li><a href="spubm1m.php" style="color: #FFFFFF;font-size: 18px;text-decoration: none;">SPUB/M1M</a></li>
            <li><a href="maintenance.php">Maintenance</a></li>
            <li><a href="report.php">Report</a></li>
    <?php require_once 'includes/menuFooter.php';?>
</div>

    <div id="content" align="center">
        <table id="maintenance-main-table" width="80%" align="center">
            <tr>
                <th>SPUB</th>
                <th></th>
            </tr>
            <tr>
                <td><li><a href="spub/spubIn.php">SPUB In</a></li></td>
                <td><li><a href="spub/spubOut.php">SPUB Out</a></li></td>
            </tr>
            <tr>
                <td><li><a href="spub/spubPermenant.php">SPUB Permanent</a></li></td>
                <td></td>
            </tr>
            <tr>
                <th>M1M</th>
                <th></th>
            </tr>
            <tr>
                <td><li><a href="spub/m1mIn.php">M1M In</a></li></td>
                <td><li><a href="spub/m1mOut.php">M1M Out</a></li></td>
            </tr>
        </table>
    </div>     
    <?php require_once 'includes/pageFooter.php';?>
</body>
</html>
