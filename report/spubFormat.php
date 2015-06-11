<!DOCTYPE html>
<html>
<head>
    <title>MethaSys-Report</title>
    <link rel="stylesheet" type="text/css" href="../css/spubFormatStyle.css">
    <script src="../scripts/jquery-1.11.2.min.js"></script>
    <script>
        function displaySPUBFormat(){
            var spubWin = window.open('','retenWindow','fullscreen=yes');
            spubWin.document.write('<html><head><title>MethaSys-Log</title><link rel="stylesheet" type="text/css" href="../css/spubFormatStyle.css"><style type="text/css" media="print">@page {size: landscape;}</style></head><body>');
            spubWin.document.write($("#format").html());
            spubWin.document.write('</body></html>');
            spubWin.print();
        }
    </script>
</head>
<body>
<div id="header">
    <?php
        include '../includes/header.php';
    ?>
</div>
    <div id="content">
        <button onclick="displaySPUBFormat()">Print</button>
        <div id="A4-size">
            <div id="format">
                <p>FORMAT RUJUKAN PRESKRIPSI METHADONE</p>
                <table id="spub-table">
                    <tr>
                        <td colspan="2">Fasility yang merujuk</td>
                        <td colspan="2">Fasility yang dirujuk</td>
                    </tr>
                    <tr>
                        <td>Negeri:</td>
                        <td></td>
                        <td>Negeri:</td>
                        <td></td>
                    </tr>
                    <tr>
                        <td>Pusat Rawatan:</td>
                        <td></td>
                        <td>Pusat Rawatan:</td>
                        <td></td>
                    </tr>
                    <tr>
                        <td>Alamat:</td>
                        <td></td>
                        <td>Alamat:</td>
                        <td></td>
                    </tr>
                    <tr>
                        <td>No. Tel:</td>
                        <td></td>
                        <td>No. Tel:</td>
                        <td></td>
                    </tr>
                    <tr>
                        <td>No. Faks:</td>
                        <td></td>
                        <td>No. Faks:</td>
                        <td></td>
                    </tr>
                </table>
                <div id="patient-info">
                    <p>A.   Maklumat Pesakit:</p>
                    <p>Nama : <label></label></p>
                    <p>No. ID : <label></label>Umur:Jantina: L /P</p>
                </div>
            </div>
        </div> 
    </div>     
    <?php require_once '../includes/pageSubFooter.php';?>
</body>
</html>
