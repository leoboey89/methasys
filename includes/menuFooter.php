<!DOCTYPE html>
<html>
<head>
</head>
<body>
    </ul>
    </div>
    <div style="width:1200px;margin:10px auto;padding:0px 0px 0px 10px;font-size: 20px;font-weight:bold;text-align:right;">
        Welcome <?php echo $_COOKIE['user'];?>! 
        <label style="font-size: 16px;font-weight:none;">Your login time is <?php echo $_COOKIE['time']?></label>
        <input type="button" onclick="parent.location='/methasys/main.php'" id="logout" value="Logout">
    </div>


</body>
</html>
