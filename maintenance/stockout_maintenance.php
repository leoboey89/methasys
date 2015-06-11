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
    $selectedStock = 'Create New Stock Out';
    $missing = array();

    $stockInExist = $db->isExist('stockin_hist', ['1']);
    $stockOutExist = $db->isExist('stockout_hist', ['1']);
    $stockIdResult = $db->getResultSet('stockout_hist', ['stockout_id', 'stockout_date', 'stockout_volume', 'date_created'], 
        ['stockout_date >= "' . $todayDate . '"', 'stockout_date < "'. $tomorrowDate . '"', 'stockout_kk = "' . $kk . '"'], 
        ['stockout_id'], ['stockout_id']);

    if ($stockInExist) {
        $totalStockInVolResult = $db->getResultSet('stockin_hist', ['sum(stockin_volume) as stockin_volume'], 
            ['stockin_active = "Y"', 'stockin_kk = "' . $kk . '"']);
        $totalStockInVol = $totalStockInVolResult->fetch_assoc();
        
        $totalStockInVol = (int) $totalStockInVol['stockin_volume'];
    } else {
        $totalStockInVol = 0;
    }

    if ($stockOutExist) {
        $totalStockOutVolResult = $db->getResultSet('stockout_hist', ['sum(stockout_volume) as stockout_volume'], 
            ['stockout_active = "Y"', 'stockout_kk = "' . $kk . '"']);
        $totalStockOutVol = $totalStockOutVolResult->fetch_assoc();

        $totalStockOutVol =  (int) $totalStockOutVol['stockout_volume'];
    } else {
        $totalStockOutVol =  0;
    }
    
    $totalBalVol = $totalStockInVol - $totalStockOutVol;

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $selectedDate = $_POST['date-selector'];
        $selectedStock = $_POST['stock-selector'];

        if ($_POST['date-selector'] != "") {
            $fromDate = $_POST['date-selector'];
            $newToDate = new Date($timeZone);
            $newToDate->setFromMySQL($fromDate);
            $newToDate->addDays(1);

            $stockIdResult = $db->getResultSet('stockout_hist', ['stockout_id', 'stockout_date', 'stockout_volume', 'date_created'], 
                ['stockout_date >= "' . $fromDate . '"', 'stockout_date < "'. $newToDate->getMySQLFormat() . '"', 'stockout_kk = "' . $kk . '"'], 
                ['stockout_id'], ['stockout_id']);

        }

        if (($_POST['date-selector'] != '') && ($_POST['stock-selector'] != 'Create New Stock Out')) {
            $stockVolumeResult = $db->getResultSet('stockout_hist', ['stockout_volume', 'stockout_active'], 
                ['stockout_id = "' . $selectedStock . '"', 'stockout_kk = "' . $kk . '"'], ['stockout_id'], ['stockout_id']);
        }

        if (!empty($_POST['create']) && ($_POST['create'] == 'Save')) {

            /*Create an Validator object to validate input field*/
            $validator = new Validator(['date-selector', 'stock-volume']);

            $missingInput = $validator->getMissingInput();
            /*If any compulsary field is not filled up*/
            if (!empty($missingInput)) {

                // Get missing field from Validator object
                $missing = $validator->getMissingInput();
            } else {
                /*Update patient's info*/
                date_default_timezone_set('Asia/Kuala_Lumpur');
                $date = date("Y-m-d H:i:s");
                
                $stockStatus = $db->insertData('stockout_hist',['stockout_volume', 
                    'stockout_date', 'stockout_kk', 'user_created', 'date_created'],
                    [$_POST['stock-volume'], $selectedDate, $kk, $user, $date]);
                echo '<script>
                        if(!alert("Stock ' . $_POST['stock-volume'] . 'ml entered"))
                            {window.location.replace("stockout_maintenance.php");}
                    </script>';

            }
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
                
                $stockStatus = $db->updateData('stockout_hist',['stockout_volume = "' . $_POST['stock-volume'] . '"', 
                    'stockout_active = "' . $_POST['stock-active'] . '"', 'user_updated = "' . $user . '"', 'date_updated = "' . $date . '"'],
                    ['stockout_id = "' . $_POST['stock-selector'] . '"', 'stockout_kk = "' . $kk . '"']);
                echo '<script>
                        if(!alert("Stock ID ' . $_POST['stock-selector'] . '\'s volume update to ' . $_POST['stock-volume'] . 'ml!"))
                            {window.location.replace("stockout_maintenance.php");}
                    </script>';

            }
        }
    }


?>

<!DOCTYPE html>
<html>
<head>
    <title>MethaSys-Stock Out Maintenance</title>
    <link rel="stylesheet" type="text/css" href="../css/stockMainStyle.css">
</head>
<div id="header">
    <?php require_once '../includes/titleHeader.php';?>
        <lable>MethaSys - Stock Out Maintenance Page</lable>
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
                <div id="stock-info">
                    <strong><label>Total Inventory Volume(ml): </label></strong><?php echo $totalBalVol;?>
                    <br>
                </div>
                <table id="stock-update-table">
                    <tr>
                        <th><label>Stock Out Date: </label></th>
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
                        <th><label>Stock Out ID: </label></th>
                        <td>
                        <select id="stock-selector" name="stock-selector" oninput="this.form.submit()">
                            <option>Create New Stock Out</option>
                            <?php
                                while($stockIdRow = $stockIdResult->fetch_assoc()) {
                                    if ($selectedStock == $stockIdRow['stockout_id']) {
                                        echo '<option value="' . $stockIdRow['stockout_id'] . '" selected="selected">';
                                        echo $stockIdRow['stockout_id'];
                                        echo ' >>> ';
                                        echo $stockIdRow['date_created'];
                                        echo '</option>';
                                    } else {
                                        echo '<option value="' . $stockIdRow['stockout_id'] . '">';
                                        echo $stockIdRow['stockout_id'];
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
                        <th><label>Stock Out Volume(ml): </label></th>
                        <td>
                            <input type="text" name="stock-volume" value="<?php
                                if (($_SERVER['REQUEST_METHOD'] == 'POST') && ($_POST['date-selector'] != '') && ($_POST['stock-selector'] != 'Create New Stock Out')) {
                                    $stockVolume = $stockVolumeResult->fetch_assoc();
                                    echo $stockVolume['stockout_volume'];
                                }
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
                                    if (($_SERVER['REQUEST_METHOD'] == 'POST') && ($_POST['date-selector'] != '') && ($_POST['stock-selector'] != 'Create New Stock Out')) {
                                        if ($stockVolume['stockout_active'] == 'Y') {
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
                <?php 
                    if(($_SERVER['REQUEST_METHOD'] == 'POST') && !empty($_POST['stock-selector']) && ($_POST['stock-selector'] != 'Create New Stock Out')) {
                        echo '<input type="submit" id="update" name="update" value="Update">';
                    } else {
                        echo '<input type="submit" id="create" name="create" value="Save">';
                    }
                ?>
                
            </form>
        </div>    
    </div>     
    <?php require_once '../includes/pageSubFooter.php';?>
</body>
</html>
