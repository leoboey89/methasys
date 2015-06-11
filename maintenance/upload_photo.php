<?php
    
    session_set_cookie_params(0);
 
    require_once '../classes/Date.php';
    require_once '../classes/MySQLConnector.php';
    require_once '../includes/session.php';

    // make a note of the current working directory relative to root.

    $directory_self = str_replace(basename($_SERVER['PHP_SELF']), '', $_SERVER['PHP_SELF']);

    // make a note of the location of the upload handler
    $uploadHandler = 'http://' . $_SERVER['HTTP_HOST'] . $directory_self . 'photo_uploader.php';

    // set a max file size for the html upload form
    $max_file_size = 100000; // size in bytes
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
        <form action="<?php echo $uploadHandler ?>" method="post" enctype="multipart/form-data">
            <input type="hidden" name="MAX_FILE_SIZE" value="<?php echo $max_file_size ?>">

            <label for="file">Select photos to upload(max 100kB): </label>
            <span style="color:red">Compulsary jpg format only!</span>
            <hr>
            <input type="file" name="file[]" id="file" multiple="multiple">
            <input type="submit" value="Upload Photos" name="submit">
        </form>
             
    </div>     
    <?php require_once '../includes/pageSubFooter.php';?>
</body>
</html>
