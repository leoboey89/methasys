<?php
    require_once 'includes/session.php';
?>
<!DOCTYPE html>
<html>
<head>
    <title>MethaSys-Report</title>
    <link rel="stylesheet" type="text/css" href="css/reportStyle.css">
    <script type="text/javascript"></script>
    <script src="scripts/jquery-1.11.2.min.js"></script>
    <script>
        $("document").ready(function(){
            $("#printSPUB").click(function(){
                window.open('scripts/printSPUB.php', 'retenWindow','fullscreen=yes').print();      
            });

            $("#printM1M").click(function(){
                window.open('scripts/printM1M.php', 'retenWindow','fullscreen=yes').print();      
            });
        });
    </script>
</head>
<body>
<div id="header">
    <?php require_once 'includes/titleHeader.php';?>
        <label>MethaSys - Report Page</label>
    <?php require_once 'includes/titleFooter.php';?>
    <?php require_once 'includes/menuHeader.php';?>
            <li><a href="announcement.php">Annoucement</a></li>
            <li><a href="scan.php">Scan</a></li>
            <li><a href="home.php">Log</a></li>
            <li><a href="spubm1m.php">SPUB/M1M</a></li>
            <li><a href="maintenance.php">Maintenance</a></li>
            <li><a href="report.php" style="color: #FFFFFF;font-size: 18px;text-decoration: none;">Report</a></li>
    <?php require_once 'includes/menuFooter.php';?>
</div>

    <div id="content" align="center">
        <table id="report-main-table" width="80%" align="center">
            <tr>
                <th>Master List Report</th>
                <th></th>
            </tr>
            <tr>
                <td><li><a href="report/masterList.php">Master List</a></li></td>
                <td><li><a href="report/absenteeList.php">Absentee List</a></li></td>
            </tr>
            <tr>
                <th>Reten Report</th>
                <th></th>
            </tr>
            <tr>
                <td><li><a href="report/reten.php">Reten</a></li></td>
                <td><li><a href="report/registerReport.php">Register</a></li></td>
            </tr>
            <tr>
                <td><li><a href="report/transferInReport.php">Transfer In</a></li></td>
                <td><li><a href="report/reactivateReport.php">Reactivate</a></li></td>
            </tr>
            <tr>
                <td><li><a href="report/suspendedReport.php">Suspended</a></li></td>
                <td><li><a href="report/lostReport.php">Lost</a></li></td>
            </tr>
            <tr>
                <td><li><a href="report/terminatedReport.php">Terminated</a></li></td>
                <td><li><a href="report/deathReport.php">Death</a></li></td>
            </tr>
            <tr>
                <td><li><a href="report/transferOutReport.php">Transfer out</a></li></td>
                <td><li><a href="report/stockReport.php">Stock</a></li></td>
            </tr>
            <tr>
                <td><li><a href="report/dotReport.php">DOT</a></li></td>
                <td><li><a href="report/dbbReport.php">DBB</a></li></td>
            </tr>
            <tr>
                <td><li><a href="report/doseChangeReport.php">Patient Dose History</a></li></td>
                <td></td>
            </tr>
            <tr>
                <th>M1M Report</th>
                <th></th>
            </tr>
            <tr>
                <td><li><a href="report/m1mInReport.php">M1M In</a></li></td>
                <td><li><a href="report/m1mOutReport.php">M1M Out</a></li></td>
            </tr>
            <tr>
                <th>SPUB Report</th>
                <th></th>
            </tr>
            <tr>
                <td><li><a href="report/spubInReport.php">SPUB In</a></li></td>
                <td><li><a href="report/spubOutReport.php">SPUB Out</a></li></td>
            </tr>
            <tr>
                <th>Reprint Format</th>
                <th></th>
            </tr>
            <tr>
                <td><li><button id="printM1M">Card M1M Record</button></li></td>
                <td><li><button id="printSPUB">Methadone Prescription Reference Format</button></li></td>
            </tr>
        </table>
    </div>      
    <?php require_once 'includes/pageFooter.php';?>
</body>
</html>
