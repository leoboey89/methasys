<?php
    session_set_cookie_params(0);

    if (!isset($_COOKIE['user'])) {
        echo '<script>if(!alert("Session log out after 30 minutes inactive, please log in again.")){
                window.location.replace("/methasys/main.php");}</script>';
        die();
    } else {
        setcookie("user", $_COOKIE['user'], time() + 30*60, "/");
        setcookie("time", $_COOKIE['time'], time() + 30*60, "/");
        setcookie("kk", $_COOKIE['kk'], time() + 30*60, "/");
    }
?>
