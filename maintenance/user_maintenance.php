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

    $user = $_COOKIE['user'];
    $kk = $_COOKIE['kk'];

    $isAdmin = $db->isExist('user_mstr', ['user_name = "' . $user . '"', 'user_auth = "admin"']);

    /*Initialize variables*/
    $userSelect = '';
    $userName = '';
    $userFullName = '';
    $userMobile = '';
    $userActive = '';
    $userCreated = '';
    $userUpdated = '';
    $dateCreated = '';
    $dateUpdated = '';
    $missing = array();


    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        if($isAdmin) {
            $result = $db->getResultSet(['user_mstr a', 'kk_mstr b'], ['user_id', 'user_name', 
                'user_fullname', 'user_mobile', 'user_active', 'kk_name', 
                'a.user_created', 'a.user_updated', 'a.date_created', 'a.date_updated'], 
                ['user_id="' . $_POST['user-select'] . '"', 'user_kk = kk_code'], ['user_id'], ['user_id']);
        } else {
            $result = $db->getResultSet(['user_mstr a', 'kk_mstr b'], ['user_id', 'user_name', 
                'user_fullname', 'user_mobile', 'user_active', 'kk_name', 
                'a.user_created', 'a.user_updated', 'a.date_created', 'a.date_updated'], 
                ['user_id="' . $_POST['user-select'] . '"', 'user_kk = kk_code'], ['user_id'], ['user_id']);
        }
        
        $userRow = $result->fetch_assoc();
        $userSelect = $_POST['user-select'];
        $userName = $userRow['user_name'];
        $userFullName = $userRow['user_fullname'];
        $userMobile = $userRow['user_mobile'];
        $userActive = $userRow['user_active'];
        $userKk = $userRow['kk_name'];
        $userCreated = $userRow['user_created'];
        $userUpdated = $userRow['user_updated'];
        $dateCreated = $userRow['date_created'];
        $dateUpdated = $userRow['date_updated'];

        /*When Create button is pressed*/
        if (!empty($_POST['update']) && $_POST['update'] == 'Create') {
            /*Configure current datetimezone and get current time*/
            date_default_timezone_set('Asia/Kuala_Lumpur');
            $date = date("Y-m-d H:i:s");

            /*Transform input value to Uppercase*/
            $_POST['user-fullname'] = strtoupper($_POST['user-fullname']);
            $_POST['user-active'] = strtoupper($_POST['user-active']);

            /*Check existence of entered methaytype name*/
            $checkExistence = $db->isExist('user_mstr',['user_name="' . $_POST['user-name'] . '"']);

            /*If record existed*/
            if ($checkExistence) {
                /*Alert user record not saved*/
                echo '<script type="text/javascript">alert("Failed to create user, ' . $_POST['user-name'] . '! Record existed!");</script>';
            } else {
                // Initialize username and password for new user
                $username = $_POST['user-name'];
                $password = '123456';

                // A higher "cost" is more secure but consumes more processing power
                $securityLevel = 10;

                // Create a random salt
                $salt = strtr(base64_encode(mcrypt_create_iv(16, MCRYPT_DEV_URANDOM)), '+', '.');

                // Prefix information about the hash so PHP knows how to verify it later.
                // "$2a$" Means we're using the Blowfish algorithm. The following two digits are the cost parameter.
                $salt = sprintf("$2a$%02d$", $securityLevel) . $salt;

                // Hash password with the salt
                $hash = crypt($password, $salt);

                /*Create new methatype and insert into database*/
                $insertStatus = $db->insertData('user_mstr',
                    ['user_name', 'user_pwd', 'user_fullname', 'user_mobile', 
                    'user_active', 'user_kk', 'user_created', 'date_created'], 
                    [$_POST['user-name'], $hash, $_POST['user-fullname'], $_POST['user-mobile'], 
                    $_POST['user-active'], $kk, $user, $date]);
                /*If insert succesful*/
                if ($insertStatus) {
                    /*Alert successful status*/
                    echo '<script type="text/javascript">alert("New user ' . $_POST['user-name'] . ' successfully created!");</script>';
                } else {
                    echo '<script type="text/javascript">alert("Failed to create user ' . $_POST['user-name'] . '!");</script>';
                }
            }

            /*Initialize variables*/
            $_POST['user-select'] = '';
            $userSelect = '';
            $userName = '';
            $userFullName = '';
            $userMobile = '';
            $userActive = '';
            $userkk = '';
            $userCreated = '';
            $userUpdated = '';
            $dateCreated = '';
            $dateUpdated = '';

            
        } /*When Reset Password button is pressed*/
        else if (!empty($_POST['reset'])) {
            date_default_timezone_set('Asia/Kuala_Lumpur');
            $date = date("Y-m-d H:i:s");

            // Initialize username and password for new user
            $username = $_POST['user-name'];
            $password = '123456';

            // A higher "cost" is more secure but consumes more processing power
            $securityLevel = 10;

            // Create a random salt
            $salt = strtr(base64_encode(mcrypt_create_iv(16, MCRYPT_DEV_URANDOM)), '+', '.');

            // Prefix information about the hash so PHP knows how to verify it later.
            // "$2a$" Means we're using the Blowfish algorithm. The following two digits are the cost parameter.
            $salt = sprintf("$2a$%02d$", $securityLevel) . $salt;

            // Hash password with the salt
            $hash = crypt($password, $salt);

            $updateStatus = $db->updateData('user_mstr',['user_pwd="' . $hash . '"',
                'user_updated="' . $user . '"', 
                'date_updated="' . $date . '"'], 
                ['user_id="' . $_POST['user-select'] .'"']);
            if ($updateStatus) {
                echo '<script type="text/javascript">alert("' . $_POST['user-name'] . '\'s password successfully reset! Kindly logout and set your new password!");</script>';
            } else {
                echo '<script type="text/javascript">alert("Failed to reset user ' . $_POST['user-name'] . '\'s password!");</script>';
            }

            /*Initialize variables*/
            $_POST['user-select'] = '';
            $userSelect = '';
            $userName = '';
            $userFullName = '';
            $userMobile = '';
            $userActive = '';
            $userKk = '';
            $userCreated = '';
            $userUpdated = '';
            $dateCreated = '';
            $dateUpdated = '';
        } /*When Update button is pressed*/
        else if (!empty($_POST['update']) && $_POST['update'] == 'Update') {
            date_default_timezone_set('Asia/Kuala_Lumpur');
            $date = date("Y-m-d H:i:s");

            /*Transform input value to Uppercase*/
            $_POST['user-fullname'] = strtoupper($_POST['user-fullname']);
            $_POST['user-active'] = strtoupper($_POST['user-active']);

            /*Check existence of entered methaytype name*/
            $checkExistence = $db->isExist('user_mstr',['user_name="' . $_POST['user-name'] . '"',
                'user_id<>"' . $_POST['user-select'] . '"']);

            /*If record existed*/
            if ($checkExistence) {
                /*Alert user record not saved*/
                echo '<script type="text/javascript">alert("Failed to update user, ' . $_POST['user-name'] . '! Record existed!");</script>';
            } else {
                $updateStatus = $db->updateData('user_mstr',['user_name="' . $_POST['user-name'] . '"',
                    'user_fullname="' . $_POST['user-fullname'] . '"',
                    'user_mobile="' . $_POST['user-mobile'] . '"',
                    'user_active="' . $_POST['user-active'] . '"', 
                    'user_kk="' . $_POST['user-kk'] . '"',
                    'user_updated="' . $user . '"', 
                    'date_updated="' . $date . '"'], 
                    ['user_id="' . $_POST['user-select'] .'"']);
                if ($updateStatus) {
                    echo '<script type="text/javascript">alert("User ' . $_POST['user-name'] . ' successfully updated!");</script>';
                } else {
                    echo '<script type="text/javascript">alert("Failed to update user ' . $_POST['user-name'] . '!");</script>';
                }
            }

            /*Initialize variables*/
            $_POST['user-select'] = '';
            $userSelect = '';
            $userName = '';
            $userFullName = '';
            $userMobile = '';
            $userActive = '';
            $userKk = '';
            $userCreated = '';
            $userUpdated = '';
            $dateCreated = '';
            $dateUpdated = '';
        }
    }

    /*Get all user's info for maintenance purpose*/
    $result = $db->getResultSet('user_mstr', ['user_id', 'user_name', 
        'user_fullname', 'user_mobile', 'user_active', 'user_auth', 
        'user_created', 'user_updated', 'date_created', 'date_updated'], 
        ['1'], ['user_id'], ['user_id']);
    $kkResult = $db->getResultSet('kk_mstr', ['kk_name', 'kk_code'], ['1'], ['kk_name']);

?>

<!DOCTYPE html>
<html>
<head>
    <title>MethaSys-User Maintenance</title>
    <link rel="stylesheet" type="text/css" href="../css/userMainStyle.css">
    <script type="text/javascript">


    </script>
</head>
<div id="header">
    <?php require_once '../includes/titleHeader.php';?>
        <lable>MethaSys - User Maintenance Page</lable>
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
        <div id="user-maintenance">
            <form name="updateForm" method="post" autocomplete="off">
                <label id="user-label">User Name :</label>
                <select id="user-select" name="user-select" oninput="this.form.submit()">
                    <?php
                        if (trim($userSelect) == 'Create New') {
                            echo '<option selected="selected" value="Create New">Create New</option>';
                        } else {
                            if($isAdmin) {
                                echo '<option value="Create New">Create New</option>';    
                            }
                        }

                        if ($isAdmin) {
                            while ($row = $result->fetch_assoc()) {
                                echo '<option value="' . $row['user_id'] . '">' . $row['user_id'] . ' >>> ' . $row['user_name'] . "</option>";
                            }
                        } else {
                            while ($row = $result->fetch_assoc()) {
                                if ($row['user_name'] == $user) {
                                    echo '<option value="' . $row['user_id'] . '">' . $row['user_id'] . ' >>> ' . $row['user_name'] . "</option>";    
                                }
                            }
                        }
                        
                    ?>
                </select>
                <script>document.getElementById("user-select").value = "<?php echo trim($userSelect);?>";</script> 
                <div id="user-table-wrapper">
                    <table id="user-update-table" style="visibility:
                        <?php 
                            if (($_SERVER['REQUEST_METHOD'] == 'POST') && (trim($_POST['user-select']) != '')) {
                                echo "visible";
                            } else { 
                                echo "hidden";
                            }
                        ?>;">
                        <tr>
                            <th>Login ID</th>
                            <td>
                                <input type="text" id="user-name" name="user-name">
                            </td>
                            <script>document.getElementById("user-name").value = "<?php echo $userName;?>"</script>    
                        </tr>
                        <tr>
                            <th>Full Name</th>
                            <td>
                                <input type="text" id="user-fullname" name="user-fullname" class="bigCap">
                            </td>
                            <script>document.getElementById("user-fullname").value = "<?php echo $userFullName;?>"</script>    
                        </tr>
                        <tr>
                            <th>Mobile Number</th>
                            <td>
                                <input type="text" id="user-mobile" name="user-mobile" class="bigCap">
                            </td>
                            <script>document.getElementById("user-mobile").value = "<?php echo $userMobile;?>"</script>    
                        </tr>
                        <tr>
                            <th>Active</th>
                            <td>
                                <select id="user-active" name="user-active">
                                    <?php if (trim($userActive) == 'Y') {
                                            echo '<option selected="selected" value="Y">Y</option>';
                                            echo '<option value="N">N</option>';
                                        } else if (trim($userActive) == 'N'){
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
                            <th>KK</th>
                            <td>
                                <select id="user-kk" name="user-kk" <?php 
                                    if (!$isAdmin) {
                                        echo 'readonly="readonly"';
                                    }
                                ?>>
                                    <?php 
                                        if (!$isAdmin) {
                                            while($row = $kkResult->fetch_assoc()) {
                                                if ($row['kk_name'] == $userKk) {
                                                    echo '<option selected="selected" value="' . $row['kk_code'] . '">' . $row['kk_name'] . '</option>';
                                                } 
                                            }
                                        } else {
                                            while($row = $kkResult->fetch_assoc()) {
                                                if ($row['kk_name'] == $userKk) {
                                                    echo '<option selected="selected" value="' . $row['kk_code'] . '">' . $row['kk_name'] . '</option>';
                                                } else {
                                                    echo '<option value="' . $row['kk_code'] . '">' . $row['kk_name'] . '</option>';    
                                                }
                                            }
                                        }
                                    ?>
                                </select>
                            </td>
                            
                        </tr>
                        <tr>
                            <th>User Created</th>
                            <td>
                                <input type="text" id="user-created" name="user-created" readonly="readonly">
                            </td>
                            <script>document.getElementById("user-created").value = "<?php echo $userCreated;?>"</script>  
                        </tr>
                        <tr>
                            <th>User Updated</th>
                            <td>
                                <input type="text" id="user-updated" name="user-updated" readonly="readonly">
                            </td>
                            <script>document.getElementById("user-updated").value = "<?php echo $userUpdated;?>"</script>  
                        </tr>
                        <tr>
                            <th>Date Created</th>
                            <td>
                                <input type="text" id="date-created" name="date-created" readonly="readonly">
                            </td>
                            <script>document.getElementById("date-created").value = "<?php echo $dateCreated;?>"</script>  
                        </tr>
                        <tr>
                            <th>Date Updated</th>
                            <td>
                                <input type="text" id="date-updated" name="date-updated" readonly="readonly">
                            </td>
                            <script>document.getElementById("date-updated").value = "<?php echo $dateUpdated;?>"</script>  
                        </tr>
                    </table>
                    <br>
                    <input type="submit" id="update" name="update" value="Update" style="visibility:
                        <?php if (($_SERVER['REQUEST_METHOD'] == 'POST') && (trim($_POST['user-select']) != '')) {
                                echo "visible";
                            } else { 
                                echo "hidden";
                            }
                        ?>;">
                        <script>
                            var code = document.getElementById("user-select");
                            if (code.value.trim() === "Create New") {
                                document.getElementById("update").value = "Create";
                            } else {
                                document.getElementById("update").value = "Update";
                            }
                        </script>
                    <input type="submit" id="reset" name="reset" value="Reset Password" style="visibility:
                            <?php if (($_SERVER['REQUEST_METHOD'] == 'POST') && (trim($_POST['user-select']) != '')) {
                                    echo "visible";
                                } else { 
                                    echo "hidden";
                                }
                            ?>;">  
                </div>
            </form>
        </div>    
    </div>     
    <?php require_once '../includes/pageSubFooter.php';?>
    <script>
        document.getElementById("user-name").focus();
    </script>  
</body>
</html>
