<?php
    require_once 'classes/MySQLConnector.php';
    require_once 'classes/Validator.php';
    require_once 'includes/session.php';

    $db = new MySQLConnector('localhost', 'leoboey_db', 'methasys2015', 'leoboey_db');
    $username = $_COOKIE['user'];
    $row = array();
    $matched = false;
    $error = array();
    $missing = array();

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    	$validator = new Validator(['password','retype-password']);

    	$missingInput = $validator->getMissingInput();
        if (!empty($missingInput)) {
            $missing = $validator->getMissingInput();
        } else {
            $validator->noFilter(['password', 'retype-password']);
            $validator->validateInput();

            $errorInput = $validator->getError();
            if (!empty($errorInput)) {
                $error = $validator->getError();
            } else {

            	if ($_POST['password'] != $_POST['retype-password']) {
            		echo '<script type="text/javascript">alert("Failed to set password! Please enter same password!");</script>';
            	} else {
            		date_default_timezone_set('Asia/Kuala_Lumpur');
				    $date = date("Y-m-d H:i:s");

				    // Initialize password for new user 
				    $password = $_POST['password'];

				    // A higher "cost" is more secure but consumes more processing power
				    $securityLevel = 10;

				    // Create a random salt
				    $salt = strtr(base64_encode(mcrypt_create_iv(16, MCRYPT_DEV_URANDOM)), '+', '.');

				    // Prefix information about the hash so PHP knows how to verify it later.
				    // "$2a$" Means we're using the Blowfish algorithm. The following two digits are the cost parameter.
				    $salt = sprintf("$2a$%02d$", $securityLevel) . $salt;

				    // Hash password with the salt
				    $hash = crypt($password, $salt);

                    $username;

				    $updateStatus = $db->updateData('user_mstr',['user_pwd="' . $hash . '"',
				        'user_updated="' . $username . '"', 
				        'date_updated="' . $date . '"'], 
				        ['user_name="' . $username .'"']);
				    if ($updateStatus) {
				    	header('Location: /methasys/main.php');
				        echo '<script type="text/javascript">alert("Password set!");</script>';      
				    } else {
				        echo '<script type="text/javascript">alert("Failed to set password!");</script>';
				    }
            	}
			}
		}
	}

?>

<!DOCTYPE html>
<html>
<head>
    <title>MethaSys-Reset Password</title>
    <link rel="stylesheet" type="text/css" href="css/resetPasswordStyle.css">
    <link rel="stylesheet" href="css/jquery-ui.css">
    <script type="text/javascript">

    </script>
</head>
<body>
<div id="header">
    <?php require_once 'includes/titleHeader.php';?>
        <label>MethaSys - Reset Password Page</label>
    <?php require_once 'includes/titleFooter.php';?>
    <?php require_once 'includes/menuHeader.php';?>
</div>

    <div id="content">
        <div id="set-password-content">
            <form name="updateForm" method="post" autocomplete="off">
                <label id="set-password-label">Please enter your new password:</label>
                <div id="set-password-table-wrapper">
                    <table id="set-password-update-table">
                        <tr>
                            <th>New Password : </th>
                            <td>
                                <input type="password" id="password" name="password" <?php if (($_SERVER['REQUEST_METHOD'] == 'POST') && (in_array('password', $missing) || is_null($row))) 
			                    { echo "autofocus";}?>>
			                    <br>
			                    <?php
			                        if (($_SERVER['REQUEST_METHOD'] == 'POST') && in_array('password', $missing)) {
			                            echo '<span style="color:#142952;font-size:12px;">Please enter new password</span>';
			                        }
			                    ?>
                            </td>
                        </tr>
                        <tr>
                            <th>Retype Password : </th>
                            <td>
                                <input type="password" id="retype-password" name="retype-password" <?php if (($_SERVER['REQUEST_METHOD'] == 'POST') && (in_array('retype-password', $missing) || is_null($row))) 
			                    { echo "autofocus";}?>>
			                    <br>
			                    <?php
			                        if (($_SERVER['REQUEST_METHOD'] == 'POST') && in_array('retype-password', $missing)) {
			                            echo '<span style="color:#142952;font-size:12px;">Please retype new password</span>';
			                        }
			                    ?>
                            </td>
                        </tr>
                    </table>
                    <br>
                    <input type="submit" id="set" name="set" value="Set New Password">  
                </div>
            </form>
        </div>    
    </div> 
    <?php require_once 'includes/pageFooter.php';?>
</body>
</html>