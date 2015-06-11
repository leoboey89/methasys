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

    /*Initialize variables*/
    $methatypeSelect = '';
    $methatypeName = '';
    $methatypeActive = '';
    $userCreated = '';
    $userUpdated = '';
    $dateCreated = '';
    $dateUpdated = '';
    $missing = array();


    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $result = $db->getResultSet('methatype_mstr', ['methatype_id', 'methatype_name', 
            'methatype_active', 'user_created', 'user_updated', 'date_created',
            'date_updated'], 
            ['methatype_id="' . $_POST['methatype-select'] . '"'], ['methatype_id'], ['methatype_name']);
        $methatypeRow = $result->fetch_assoc();
        $methatypeSelect = $_POST['methatype-select'];
        $methatypeName = $methatypeRow['methatype_name'];
        $methatypeActive = $methatypeRow['methatype_active'];
        $userCreated = $methatypeRow['user_created'];
        $userUpdated = $methatypeRow['user_updated'];
        $dateCreated = $methatypeRow['date_created'];
        $dateUpdated = $methatypeRow['date_updated'];

        /*When Create button is pressed*/
        if (!empty($_POST['update']) && $_POST['update'] == 'Create') {
            /*Configure current datetimezone and get current time*/
            date_default_timezone_set('Asia/Kuala_Lumpur');
            $date = date("Y-m-d H:i:s");

            /*Transform input value to Uppercase*/
            $_POST['methatype-name'] = strtoupper($_POST['methatype-name']);
            $_POST['methatype-active'] = strtoupper($_POST['methatype-active']);

            /*Check existence of entered methaytype name*/
            $checkExistence = $db->isExist('methatype_mstr',['methatype_name="' . $_POST['methatype-name'] . '"']);

            /*If record existed*/
            if ($checkExistence) {
                /*Alert user record not saved*/
                echo '<script type="text/javascript">alert("Failed to create methatype, ' . $_POST['methatype-name'] . '! Record existed!");</script>';
            } else {
                /*Create new methatype and insert into database*/
                $insertStatus = $db->insertData('methatype_mstr',['methatype_name', 
                    'methatype_active', 'user_created', 'date_created'],
                    [$_POST['methatype-name'], $_POST['methatype-active'], $_COOKIE['user'], 
                    $date]);
                /*If insert succesful*/
                if ($insertStatus) {
                    /*Alert successful status*/
                    echo '<script type="text/javascript">alert("New methatype ' . $_POST['methatype-name'] . ' successfully created!");</script>';
                } else {
                    echo '<script type="text/javascript">alert("Failed to create methatype ' . $_POST['methatype-name'] . '!");</script>';
                }
            }

            /*Initialize variables*/
            $_POST['methatype-select'] = '';
            $methatypeSelect = '';
            $methatypeName = '';
            $methatypeActive = '';
            $userCreated = '';
            $userUpdated = '';
            $dateCreated = '';
            $dateUpdated = '';

            
        } /*When Update button is pressed*/
        else if (!empty($_POST['update']) && $_POST['update'] == 'Update') {
            date_default_timezone_set('Asia/Kuala_Lumpur');
            $date = date("Y-m-d H:i:s");

            /*Transform input value to Uppercase*/
            $_POST['methatype-name'] = strtoupper($_POST['methatype-name']);

            /*Check existence of entered methaytype name*/
            $checkExistence = $db->isExist('methatype_mstr',['methatype_name="' . $_POST['methatype-name'] . '"',
                'methatype_id<>"' . $_POST['methatype-select'] . '"']);

            /*If record existed*/
            if ($checkExistence) {
                /*Alert user record not saved*/
                echo '<script type="text/javascript">alert("Failed to update methatype, ' . $_POST['methatype-name'] . '! Record existed!");</script>';
            } else {
                $updateStatus = $db->updateData('methatype_mstr',['methatype_name="' . $_POST['methatype-name'] . '"',
                    'methatype_active="' . $_POST['methatype-active'] . '"', 'user_updated="' . $_COOKIE['user'] . '"', 'date_updated="' . $date . '"'], 
                    ['methatype_id="' . $_POST['methatype-select'] .'"']);
                if ($updateStatus) {
                    echo '<script type="text/javascript">alert("Methatype ' . $_POST['methatype-name'] . ' successfully updated!");</script>';
                } else {
                    echo '<script type="text/javascript">alert("Failed to update methatype ' . $_POST['methatype-name'] . '!");</script>';
                }
            }

            /*Initialize variables*/
            $_POST['methatype-select'] = '';
            $methatypeSelect = '';
            $methatypeName = '';
            $methatypeActive = '';
            $userCreated = '';
            $userUpdated = '';
            $dateCreated = '';
            $dateUpdated = '';
        }
    }

    /*Get all methatype's info for maintenance purpose*/
    $result = $db->getResultSet('methatype_mstr', ['methatype_id', 'methatype_name', 
        'methatype_active', 'user_created', 'user_updated', 'date_created',
        'date_updated'], 
        ['1'], ['methatype_id'], ['methatype_id']);
?>

<!DOCTYPE html>
<html>
<head>
    <title>MethaSys-Methatype Maintenance</title>
    <link rel="stylesheet" type="text/css" href="../css/methatypeMainStyle.css">
    <script type="text/javascript">


    </script>
</head>
<div id="header">
    <?php require_once '../includes/titleHeader.php';?>
        <label>MethaSys - Methatype Maintenance Page</label>
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
        <div id="methatype-maintenance">
            <form name="updateForm" method="post" autocomplete="off">
                <label id="methatype-label">Methatype :</label>
                <select id="methatype-select" name="methatype-select" oninput="this.form.submit()">
                    <?php
                        if (trim($methatypeSelect) == 'Create New') {
                            echo '<option selected="selected" value="Create New">Create New</option>';
                        } else {
                            echo '<option value="Create New">Create New</option>';
                        }
                        while ($row = $result->fetch_assoc()) {
                            echo '<option value="' . $row['methatype_id'] . '">' . $row['methatype_id'] . ' >>> ' . $row['methatype_name'] . "</option>";
                        }
                    ?>
                </select>
                <script>document.getElementById("methatype-select").value = "<?php echo trim($methatypeSelect);?>";</script> 
                <div id="methatype-table-wrapper">
                    <table id="methatype-update-table" style="visibility:
                        <?php 
                            if (($_SERVER['REQUEST_METHOD'] == 'POST') && (trim($_POST['methatype-select']) != '')) {
                                echo "visible";
                            } else { 
                                echo "hidden";
                            }
                        ?>;">
                        <tr>
                            <th>Methatype Name</th>
                            <td>
                                <input type="text" id="methatype-name" name="methatype-name" class="bigCap">
                            </td>
                            <script>document.getElementById("methatype-name").value = "<?php echo $methatypeName;?>"</script>    
                        </tr>
                        <tr>
                            <th>Active</th>
                            <td>
                                <select id="methatype-active" name="methatype-active">
                                    <?php if (trim($methatypeActive) == 'Y') {
                                            echo '<option selected="selected" value="Y">Y</option>';
                                            echo '<option value="N">N</option>';
                                        } else if (trim($methatypeActive) == 'N'){
                                            echo '<option value="Y">Y</option>';
                                            echo '<option selected="selected" value="N">N</option>';
                                        } else {
                                            echo '<option value="Y">Y</option>';
                                            echo '<option value="N">N</option>';
                                        }
                                    ?>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th>User Created</th>
                            <td>
                                <input type="text" id="methatype-user-created" name="methatype-user-created" readonly="readonly">
                            </td>
                            <script>document.getElementById("methatype-user-created").value = "<?php echo $userCreated;?>"</script>  
                        </tr>
                        <tr>
                            <th>User Updated</th>
                            <td>
                                <input type="text" id="methatype-user-updated" name="methatype-user-updated" readonly="readonly">
                            </td>
                            <script>document.getElementById("methatype-user-updated").value = "<?php echo $userUpdated;?>"</script>  
                        </tr>
                        <tr>
                            <th>Date Created</th>
                            <td>
                                <input type="text" id="methatype-date-created" name="methatype-date-created" readonly="readonly">
                            </td>
                            <script>document.getElementById("methatype-date-created").value = "<?php echo $dateCreated;?>"</script>  
                        </tr>
                        <tr>
                            <th>Date Updated</th>
                            <td>
                                <input type="text" id="methatype-date-updated" name="methatype-date-updated" readonly="readonly">
                            </td>
                            <script>document.getElementById("methatype-date-updated").value = "<?php echo $dateUpdated;?>"</script>  
                        </tr>
                    </table>
                    <br>
                    <input type="submit" id="update" name="update" value="Update" style="visibility:
                        <?php if (($_SERVER['REQUEST_METHOD'] == 'POST') && (trim($_POST['methatype-select']) != '')) {
                                echo "visible";
                            } else { 
                                echo "hidden";
                            }
                        ?>;">
                        <script>
                            var code = document.getElementById("methatype-select");
                            if (code.value.trim() === "Create New") {
                                document.getElementById("update").value = "Create";
                            } else {
                                document.getElementById("update").value = "Update";
                            }
                        </script>  
                </div>
            </form>
        </div>    
    </div>     
    <?php require_once '../includes/pageSubFooter.php';?>
    <script>
        document.getElementById("methatype-name").focus();
    </script>  
</body>
</html>
