<?php
    setcookie("user", "", time() - 3600, "/");
    setcookie("time", "", time() - 3600, "/");
    setcookie("kk", "", time() - 3600, "/");

    require_once "classes/Date.php";
    require_once "classes/Validator.php";
    require_once "classes/MySQLConnector.php";
    require_once "classes/Password.php";
    
    $db = new MySQLConnector('localhost', 'leoboey_db', 'methasys2015', 'leoboey_db');

    $row = array();
    $matched = false;
    $error = array();
    $missing = array();
    $username = '';

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $username = $_POST['username'];
        $validator = new Validator(['username','password']);

        $missingInput = $validator->getMissingInput();
        if (!empty($missingInput)) {
            $missing = $validator->getMissingInput();
        } else {
            $validator->noFilter(['username', 'password']);
            $validator->validateInput();

            $errorInput = $validator->getError();
            if (!empty($errorInput)) {
                $error = $validator->getError();
            } else {
                $username = $_POST['username'];
                $password = $_POST['password'];

                // For brevity, code to establish a database connection has been left out
                try {
                    $dbh = new PDO('mysql:dbname=leoboey_db;host=localhost', 'leoboey_db', 'methasys2015');
                } catch (PDOException $e) {
                    echo 'Connection failed: ' . $e->getMessage();
                }
                
                $spw = $dbh->prepare('SELECT user_pwd,user_kk FROM user_mstr WHERE user_name = :username and user_active = "Y" LIMIT 1');

                $spw->bindParam(':username', $username);

                $spw->execute();

                $user = $spw->fetch(PDO::FETCH_OBJ);     

                $enterPwd = crypt($password, $user->user_pwd);
                $usrPwd = $user->user_pwd;
                $kk = $user->user_kk;

                $ret = strlen($usrPwd) ^ strlen($enterPwd);
                $ret |= array_sum(unpack("C*", $usrPwd^$enterPwd));  

                // Hashing the password with its hash as the salt returns the same hash
                if (!$ret) {
                    if ($password == '123456') {
                        setcookie("user", $_POST['username'], time() + 5, "/");
                        header('Location: /methasys/ResetPassword.php');
                    } else {
                        session_set_cookie_params(0);
                        date_default_timezone_set('Asia/Kuala_Lumpur');
                        $time = date("Y-m-d H:i:s");

                        setcookie("user", $_POST['username'], time() + 5, "/");
                        setcookie("time", $time, time() + 5, "/");
                        setcookie("kk", $kk, time() + 5, "/");
                        header('Location: /methasys/announcement.php');
                    }
                    
                } else {
                    setcookie("user", "", time() - 3600, "/");
                    setcookie("time", "", time() - 3600, "/");
                    setcookie("kk", "", time() - 3600, "/");
                }
            }
        }
    }
?>

<!DOCTYPE html>
<html>
<head>
    <title>MethaSys-Login</title>
    <meta charset="UTF-8">
    <link rel="stylesheet" type="text/css" href="/methasys/css/loginStyle.css">
</head>
<body>
    <div id="header">
        <div id="header-title">
            MethaSys-Login Page
        </div>
    </div>
    <div id="container">
        <form action="" method="post" autocomplete="off">

            <div id="login-info">
                <p>
                    <label for="username">User Name:</label>
                    <input type="text" id="username" name="username" placeholder="Enter your user name"
                    <?php if (($_SERVER['REQUEST_METHOD'] == 'POST') && (in_array('username', $missing) || is_null($row))) 
                    { echo "autofocus";}?>>
                    <br>
                    <?php
                        if (($_SERVER['REQUEST_METHOD'] == 'POST') && in_array('username', $missing)) {
                            echo '<span style="color:#BEC2C6;padding-left:140px;">Please fill in user name</span>';
                        }
                        /*else if (($_SERVER['REQUEST_METHOD'] == 'POST') && is_null($row)) {
                            echo '<span style="color:red">Invalid User Name</span>';
                        }*/
                    ?>
                    <script type="text/javascript">
                        document.getElementById('username').value = "<?php echo $username;?>";
                    </script>
                </p>
                <p>
                    <label for="password">Password:</label>
                    <input type="password" id="password" name="password" placeholder="Enter your password"
                    <?php if (($_SERVER['REQUEST_METHOD'] == 'POST') && (in_array('password', $missing) || !$matched)) 
                    { echo "autofocus";}?>>
                    <br>
                    <?php
                        if (($_SERVER['REQUEST_METHOD'] == 'POST') && in_array('password', $missing)) {
                            echo '<span style="color:#BEC2C6;padding-left:140px;">Please fill in password</span>';
                        } else if (($_SERVER['REQUEST_METHOD'] == 'POST') && !$matched) {
                            echo '<span style="color:#BEC2C6;padding-left:140px;">Invalid Password</span>';
                        }
                    ?>
                    <br>
                </p>
                <input id="submit" type="submit" value="Submit">
            </div>
        </form>
    </div>
</body>
</html>