<?php
    header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
    header("Cache-Control: no-cache");
    header("Pragma: no-cache");
    
    session_set_cookie_params(0);
 
    require_once '../classes/Date.php';
    require_once '../classes/MySQLConnector.php';
    require_once '../classes/Validator.php';
    require_once '../includes/session.php';

    $user = $_COOKIE['user'];
    $db = new MySQLConnector('localhost', 'leoboey_db', 'methasys2015', 'leoboey_db');
    $anncID = $_GET['id'];
    $userFromAnnc = $_GET['user'];
    $kk = $_COOKIE['kk'];
    $result = false;

    if (!empty($anncID)) {
        $result = $db->getResultSet('annc_hist',['annc_id','annc_title', 'annc_message', 'user_created', 'date_created'], 
            ["annc_id = '$anncID'", "annc_kk = '$kk'"]);
        $row = $result->fetch_assoc();
    }

    if ($_SERVER['REQUEST_METHOD'] == 'POST'){
        if (!empty($_POST['create'])) {
            /*Create an Validator object to validate input field*/
            $validator = new Validator(['annc-title', 'annc-message', 'annc-author']);

            $missingInput = $validator->getMissingInput();
            /*If any compulsary field is not filled up*/
            if (!empty($missingInput)) {
                
                /*Get missing field from Validator object*/
                $missing = $validator->getMissingInput();
            } else {
                date_default_timezone_set('Asia/Kuala_Lumpur');
                $date = date("Y-m-d H:i:s");
                $title = $_POST['annc-title'];
                $message = $_POST['annc-message'];
                $username = $_POST['annc-author'];

                $insertStatus = $db->insertData('annc_hist', ['annc_title', 'annc_message', 'annc_kk', 'user_created', 'date_created'], [$title, $message, $kk, $username, $date]);
                if ($insertStatus) {
                    echo '<script>alert("Annoucement created! Please refer annoucement page")</script>';                
                }
            }
        } else if (!empty($_POST['update'])) {
            /*Create an Validator object to validate input field*/
            $validator = new Validator(['annc-title', 'annc-message', 'annc-author']);

            $missingInput = $validator->getMissingInput();
            /*If any compulsary field is not filled up*/
            if (!empty($missingInput)) {
                
                /*Get missing field from Validator object*/
                $missing = $validator->getMissingInput();
            } else {
                date_default_timezone_set('Asia/Kuala_Lumpur');
                $date = date("Y-m-d H:i:s");
                $title = $_POST['annc-title'];
                $message = $_POST['annc-message'];
                $username = $_POST['annc-author'];

                $updateStatus = $db->updateData('annc_hist', ["annc_title = '$title'", "annc_message = '$message'"], ["annc_id = '$anncID'", "annc_kk = '$kk'"]);
                if ($updateStatus) {   
                    // current working directory
                    $selfDirectory = str_replace('maintenance/' . basename($_SERVER['PHP_SELF']), '', $_SERVER['PHP_SELF']);
                    $relocate = 'http://' . $_SERVER['HTTP_HOST'] . $selfDirectory . 'maintenance/annc_maintenance.php';

                    echo '<script>alert("Annoucement updated! Please refer annoucement page");';
                    echo 'window.location.assign("' . $relocate . '");</script>';  
                }
            }
        }
    }

?>

<!DOCTYPE html>
<html>
<head>
    <title>MethaSys-Annoucement Maintenance</title>
    <link rel="stylesheet" type="text/css" href="../css/anncStyle.css">
</head>
<body>
    <div id="header">
        <?php require_once '../includes/titleHeader.php';?>
            <lable>MethaSys - Annoucement Maintenance Page</lable>
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
            <div id="title-container">
                <label>Title : </label>
                <input type="text" name="annc-title" id="annc-title" value="<?php if ($result) {
                    echo $row['annc_title'];}?>" <?php if ($result) {
                        if (trim($row['user_created']) != trim($userFromAnnc)) {
                            echo 'readonly="readonly"';
                        }
                    }?>>
                <span style="visibility:<?php
                                if (($_SERVER['REQUEST_METHOD'] == 'POST') && in_array('annc-message', $missing)) {
                                    echo 'visible';
                                }
                            ?>;">Title not inserted!</span>
            </div>
            <div id="message-container">
                <label>Annoucement Message : </label>
                <textarea name="annc-message" id="annc-message" rows="15" columns="100" <?php if ($result) {
                        if (trim($row['user_created']) != trim($userFromAnnc)) {
                            echo 'readonly="readonly"';
                        }
                    }?>><?php if ($result) {
                    echo $row['annc_message'];}?></textarea>
                <span style="visibility:<?php
                                if (($_SERVER['REQUEST_METHOD'] == 'POST') && in_array('annc-message', $missing)) {
                                    echo 'visible';
                                }
                            ?>;">Message not inserted!</span>
            </div>
            <div id="author-container">
                <label>Author : </label>
                <input type="text" name="annc-author" id="annc-author" readonly="readonly" value="<?php if ($result) {
                    echo $row['user_created'];}
                    else {
                        echo $user;
                    }?>">
            </div>
            <div id="create-container">
                <?php if ($result) {
                    if ($result) {
                        if (trim($row['user_created']) != trim($userFromAnnc)) {
                            echo '<input type="submit" name="update" id="update" value="Update" style="visibility:hidden;">';
                        } else {
                            echo '<input type="submit" name="update" id="update" value="Update">';
                        }
                    }
                        
                    } else {
                        echo '<input type="submit" name="create" id="create" value="Create">';
                    }
                ?>
                
            </div>
        </form>
    </div>     
    <?php require_once '../includes/pageSubFooter.php';?>
</body>
</html>
