<?php
    
    session_set_cookie_params(0);
 
    require_once '../classes/Date.php';
    require_once '../classes/MySQLConnector.php';
    require_once '../includes/session.php';

    // current working directory
	$selfDirectory = str_replace('maintenance/' . basename($_SERVER['PHP_SELF']), '', $_SERVER['PHP_SELF']);

    // location of the upload photo form
	$uploadForm = 'http://' . $_SERVER['HTTP_HOST'] . $selfDirectory . 'maintenance/upload_photo.php';

    header("Refresh: 3; URL=\"$uploadForm\"");

?>

<!DOCTYPE html>
<html>
	<head>
	    <title>MethaSys-Upload Photo</title>
	    <link rel="stylesheet" type="text/css" href="../css/transactionStyle.css">
	    <script src="../scripts/jquery-1.11.2.min.js"></script>
	    <style type="text/css" media="print">
	        @page {size: landscape;}
	    </style>
	</head>
	<body>
		<div id="header">
		    <?php require_once '../includes/titleHeader.php';?>
		        <lable>MethaSys - Upload Photo Page</lable>
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
			<h1>Photo upload</h1>
			<p>Congratulations! Your photos upload were successful</p>
		</div>
		<?php require_once '../includes/pageSubFooter.php';?>
	</body>

</html>