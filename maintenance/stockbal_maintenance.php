<?php
    header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
    header("Cache-Control: no-cache");
    header("Pragma: no-cache");
    
    session_set_cookie_params(0);
 
    require_once '../classes/Date.php';
    require_once '../classes/MySQLConnector.php';
    require_once '../classes/Validator.php';
    require_once '../includes/session.php';

    $db = new MySQLConnector('localhost', 'leoboey_db', 'methasys2015', 'leoboey_db');
    $timeZone = new DateTimeZone('Asia/Kuala_Lumpur');
    $user = $_COOKIE['user'];
    $kk = $_COOKIE['kk'];

    $currentDate = new Date($timeZone);
    $todayDate = $currentDate->getMySQLFormat();

    /*Get tomorrow's date*/
    $nextDate = new Date($timeZone);
    $nextDate->addDays(1);
    $tomorrowDate = $nextDate->getMySQLFormat();

    /*Initialize variables*/
    $selectedDate = $todayDate;
    $missing = array();

    $stockIdResult = $db->getResultSet('bbal_hist', ['bbal_id', 'bbal_date', 'bbal_volume', 'date_created'], 
        ['bbal_date >= "' . $todayDate . '"', 'bbal_date < "'. $tomorrowDate . '"', 'bbal_kk = "' . $kk . '"'], 
        ['bbal_id'], ['bbal_id']);

    $stockVolumeResult = $db->getResultSet('bbal_hist', ['bbal_volume', 'bbal_active'], 
        ['bbal_date >= "' . $todayDate . '"', 'bbal_date < "'. $tomorrowDate . '"', 'bbal_kk = "' . $kk . '"'], ['bbal_id'], ['bbal_id']);

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $selectedDate = $_POST['date-selector'];
        $selectedStock = $_POST['stock-selector'];

        if ($_POST['date-selector'] != "") {
            $fromDate = $_POST['date-selector'];
            $newToDate = new Date($timeZone);
            $newToDate->setFromMySQL($fromDate);
            $newToDate->addDays(1);

            $stockIdResult = $db->getResultSet('bbal_hist', ['bbal_id', 'bbal_date', 'bbal_volume', 'date_created'], 
                ['bbal_date >= "' . $fromDate . '"', 'bbal_date < "'. $newToDate->getMySQLFormat() . '"', 'bbal_kk = "' . $kk . '"'], 
                ['bbal_id'], ['bbal_id']);

        }

        if (($_POST['date-selector'] != '')) {
            $stockVolumeResult = $db->getResultSet('bbal_hist', ['bbal_volume', 'bbal_active'], 
                ['bbal_date >= "' . $fromDate . '"', 'bbal_date < "'. $newToDate->getMySQLFormat() . '"', 'bbal_kk = "' . $kk . '"'], 
                ['bbal_id'], ['bbal_id']);
        }

        if (!empty($_POST['update']) && ($_POST['update'] == 'Update')) {

            /*Create an Validator object to validate input field*/
            $validator = new Validator(['date-selector', 'stock-volume']);

            $missingInput = $validator->getMissingInput();
            /*If any compulsary field is not filled up*/
            if (!empty($missingInput)) {

                /*Get missing field from Validator object*/
                $missing = $validator->getMissingInput();
            } else {
                /*Update patient's info*/
                date_default_timezone_set('Asia/Kuala_Lumpur');
                $date = date("Y-m-d H:i:s");
                
                $stockStatus = $db->updateData('bbal_hist',['bbal_volume = "' . $_POST['stock-volume'] . '"', 
                    'bbal_active = "' . $_POST['stock-active'] . '"', 'user_updated = "' . $user . '"', 'date_updated = "' . $date . '"'],
                    ['bbal_id = "' . $_POST['stock-selector'] . '"', 'bbal_kk = "' . $kk . '"']);
                echo '<script>
                        if(!alert("Stock ID ' . $_POST['stock-selector'] . '\'s volume update to ' . $_POST['stock-volume'] . 'ml!"))
                            {window.location.replace("stockbal_maintenance.php");}
                    </script>';

            }
        }
    }


?>

<!DOCTYPE html>
<html>
<head>
    <title>MethaSys-Stock Balance Maintenance</title>
    <link rel="stylesheet" type="text/css" href="../css/stockMainStyle.css">
</head>
<div id="header">
    <?php require_once '../includes/titleHeader.php';?>
        <lable>MethaSys - Stock Balance Maintenance Page</lable>
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
        <div id="stock-maintenance">
            <form name="updateForm" method="post" autocomplete="off">
                <table id="stock-update-table">
                    <tr>
                        <th><label>Stock Balance Date: </label></th>
                        <td><input type="date" id="date-selector" name="date-selector" value="<?php echo $selectedDate;?>" oninput="this.form.submit()">   </td>
                    </tr>     
                    <tr style="margin:0px;padding:0px;">
                        <th></th>
                        <td>
                            <?php
                                if (($_SERVER['REQUEST_METHOD'] == 'POST') && in_array('date-selector', $missing)) {
                                    echo '<span style="color:red">Please fill in Date</span>';
                                }
                            ?>                                          
                        </td> 
                    </tr>
                    <tr>
                        <th><label>Stock Balance ID: </label></th>
                        <td>
                        <select id="stock-selector" name="stock-selector" oninput="this.form.submit()">
                            <?php
                                while($stockIdRow = $stockIdResult->fetch_assoc()) {
                                    if ($selectedStock == $stockIdRow['bbal_id']) {
                                        echo '<option value="' . $stockIdRow['bbal_id'] . '" selected="selected">';
                                        echo $stockIdRow['bbal_id'];
                                        echo ' >>> ';
                                        echo $stockIdRow['date_created'];
                                        echo '</option>';
                                    } else {
                                        echo '<option value="' . $stockIdRow['bbal_id'] . '">';
                                        echo $stockIdRow['bbal_id'];
                                        echo ' >>> ';
                                        echo $stockIdRow['date_created'];
                                        echo '</option>';
                                    }                            
                                }
                            ?>
                        </select>
                    </td>
                    </tr>
                    <tr>
                        <th><label>Stock Balance Volume(ml): </label></th>
                        <td>
                            <input type="text" name="stock-volume" value="<?php
                                    $stockVolume = $stockVolumeResult->fetch_assoc();
                                    echo $stockVolume['bbal_volume'];
                            ?>">
                        </td>
                    </tr>
                    <tr style="margin:0px;padding:0px;">
                        <th></th>
                        <td>
                            <?php
                                if (($_SERVER['REQUEST_METHOD'] == 'POST') && in_array('stock-volume', $missing)) {
                                    echo '<span style="color:red">Please fill in Stock Volume</span>';
                                }
                            ?>                                          
                        </td> 
                    </tr>
                    <tr>
                        <th><label>Active: </label></th>
                        <td>
                            <select id="stock-active" name="stock-active">
                                <?php
                                    if (($_SERVER['REQUEST_METHOD'] == 'POST') && ($_POST['date-selector'] != '')) {
                                        if ($stockVolume['bbal_active'] == 'Y') {
                                            echo '<option selected="selected">Y</option>';
                                            echo '<option>N</option>';
                                        } else {
                                            echo '<option>Y</option>';
                                            echo '<option selected="selected">N</option>';
                                        }
                                    } else {
                                        echo '<option>Y</option>';
                                        echo '<option>N</option>';
                                    }
                                ?>
                            </select>
                        </td>
                    </tr>
                </table>
                <input type="submit" id="update" name="update" value="Update">
            </form>
        </div>    
    </div>     
    <?php require_once '../includes/pageSubFooter.php';?>
</body>
</html>
