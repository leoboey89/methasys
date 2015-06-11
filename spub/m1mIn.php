<?php
    session_set_cookie_params(0);

    require_once '../classes/Date.php';
    require_once '../classes/MySQLConnector.php';
    require_once '../classes/Validator.php';
    require_once '../includes/session.php';

    $db = new MySQLConnector('localhost', 'leoboey_db', 'methasys2015', 'leoboey_db');

    /*Get time zone*/
    $timeZone = new DateTimeZone('Asia/Kuala_Lumpur');

    /*Get today's date*/
    $currentDate = new Date($timeZone);
    $todayDate = $currentDate->getMySQLFormat($timeZone);

    $nextDate = new Date($timeZone);
    $nextDate->addDays(1);
    $tomorrowDate = $nextDate->getMySQLFormat($timeZone);

    /*Initialize variables*/
    $patientId = '';
    $patientName = '';
    $medCenter = '';
    $dose = '';
    $volume = '';
    $presDate = $todayDate;
    $missing = array();
    $kk = $_COOKIE['kk'];
    $user = $_COOKIE['user'];

    $result = $db->getResultSet('patient_mstr', ['patient_code', 'patient_name'],
        ['patient_active = "Y"', 'patient_m1min = "Y"',
        'patient_kk = "' . $kk . '"'], ['patient_id'], ['patient_id']);

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {

        if (!empty($_POST['insert'])) {
            $patientId = strtoupper($_POST['patient-presid']);
            $patientName = strtoupper($_POST['patient-patname']);
            $medCenter = strtoupper($_POST['patient-medcenter']);
            $dose = $_POST['patient-dose'];
            $volume = $_POST['patient-volume'];
            $presDate = $_POST['patient-presdate'];

            /*Create an Validator object to validate input field*/
            $validator = new Validator(['patient-presid', 'patient-patname', 'patient-medcenter', 
            'patient-dose', 'patient-volume']);

            $missingInput = $validator->getMissingInput();
            /*If any compulsary field is not filled up*/
            if (!empty($missingInput)) {

                /*Get missing field from Validator object*/
                $missing = $validator->getMissingInput();
            } else {
                date_default_timezone_set('Asia/Kuala_Lumpur');
                $date = date("Y-m-d H:i:s");

                $checkPatientExist = $db->isExist('patient_mstr', ["patient_code = '$patientId'", "patient_kk = '$kk'"]);

                if ($checkPatientExist) {
                    $createPatientStatus = $db->updateData('patient_mstr', 
                        ["patient_name = '$patientName'", "patient_fromkk = '$medCenter'", "patient_dose = '$dose'", 
                        "patient_volume = '$volume'", "patient_active = 'Y'", "patient_status = 'M1M IN'", "user_updated = '" . $user . "'","date_updated = '$date'"],
                        ["patient_code = '$patientId'", "patient_kk = '$kk'"]);
                } else {
                    $createPatientStatus = $db->insertData('patient_mstr', 
                        ['patient_code', 'patient_name', 'patient_fromkk', 'patient_dose', 
                        'patient_volume', 'patient_m1min', 'patient_status', 'patient_kk', 
                        'user_created','date_created'],
                        [$patientId, $patientName, $medCenter, $dose, 
                        $volume, 'Y', 'M1M IN', $kk, 
                        $user, $date]);
                }

                $createM1MStatus = $db->insertData('m1min_mstr', 
                    ['m1min_patcode', 'm1min_patname', 'm1min_fromkk', 'm1min_presdate',
                    'm1min_dose', 'm1min_volume', 'm1min_active', 'm1min_kk', 
                    'user_created', 'date_created'],
                    [$patientId, $patientName, $medCenter, $presDate,
                    $dose, $volume, 'Y', $kk, 
                    $user, $date]);

                $methascanStatus = $db->insertData('methascan_hist', 
                    ['methascan_patcode', 'methascan_patname', 'methascan_dose', 'methascan_volume', 
                    'methascan_date', 'methascan_patstatus', 'methascan_dot', 'methascan_kk', 
                    'user_created','date_created'],
                    [$patientId, $patientName, $dose, $volume, 
                    $date, 'M1M IN', 'Y', $kk, 
                    $user, $date]);

                if ($createPatientStatus && $createM1MStatus && $methascanStatus) {
                    echo '<script>
                        if(!alert("Patient prescription id ' . $patientId . ' created. Patient is now M1M In, please check Log page."))
                        {window.location.replace("m1min.php");}
                    </script>';
                }

                $patientId = '';
                $patientName = '';
                $medCenter = '';
                $dose = '';
                $volume = '';
                $presDate = $todayDate;

                
            }
        } else if (!empty($_POST['proceed'])) {
            $patientId = strtoupper($_POST['patient-presid']);
            $patientName = strtoupper($_POST['patient-patname']);
            $medCenter = strtoupper($_POST['patient-medcenter']);
            $dose = $_POST['patient-dose'];
            $volume = $_POST['patient-volume'];
            $presDate = $_POST['patient-presdate'];

            /*Create an Validator object to validate input field*/
            $validator = new Validator(['patient-presid', 'patient-patname', 'patient-medcenter', 
            'patient-dose', 'patient-volume']);

            $missingInput = $validator->getMissingInput();
            /*If any compulsary field is not filled up*/
            if (!empty($missingInput)) {

                /*Get missing field from Validator object*/
                $missing = $validator->getMissingInput();
            } else {
                date_default_timezone_set('Asia/Kuala_Lumpur');
                $date = date("Y-m-d H:i:s");

                /*Check whether patient already attended for methadone*/
                $attended = $db->isExist('methascan_hist', 
                    ["methascan_patcode = '$patientId'", "methascan_date >= '$todayDate'", "methascan_date < '$tomorrowDate'", "methascan_kk = '$kk'"]);

                if ($attended) {
                    echo '<script>
                            if(!alert("Patient prescription id ' . $patientId . ' attended! Patient not allow to take methadone, please check log page."))
                                {window.location.replace("m1min.php");}
                        </script>';
                } else {
                    $updatePatientStatus = $db->updateData('patient_mstr', 
                        ["patient_name = '$patientName'", "patient_fromkk = '$medCenter'", "patient_dose = '$dose'", 
                        "patient_volume = '$volume'", "user_updated = '" . $user . "'","date_updated = '$date'", "patient_status = 'M1M IN'"],
                        ["patient_code = '$patientId'", "patient_kk = '$kk'"]);

                    $createM1MStatus = $db->insertData('m1min_mstr', 
                        ['m1min_patcode', 'm1min_patname', 'm1min_fromkk', 'm1min_presdate',
                        'm1min_dose', 'm1min_volume', 'm1min_active', 'm1min_kk', 
                        'user_created', 'date_created'],
                        [$patientId, $patientName, $medCenter, $presDate,
                        $dose, $volume, 'Y', $kk, 
                        $user, $date]);

                    $methascanStatus = $db->insertData('methascan_hist', 
                        ['methascan_patcode', 'methascan_patname', 'methascan_dose', 'methascan_volume', 
                        'methascan_date', 'methascan_patstatus', 'methascan_dot', 'methascan_kk', 
                        'user_created', 'date_created'],
                        [$patientId, $patientName, $dose, $volume, 
                        $date, 'M1M IN', 'Y', $kk, 
                        $user, $date]);

                    if ($createM1MStatus && $methascanStatus) {
                        echo '<script>
                            if(!alert("Patient with prescription id ' . $patientId . ' is now M1M In, please check Log page."))
                                {window.location.replace("m1min.php");}
                        </script>';
                    }
                }

                $patientId = '';
                $patientName = '';
                $medCenter = '';
                $dose = '';
                $volume = '';
                $presDate = $todayDate;
            }
        } else if (!empty($_POST['delete'])) {
            $patientId = strtoupper($_POST['patient-presid']);

                date_default_timezone_set('Asia/Kuala_Lumpur');
                $date = date("Y-m-d H:i:s");

                $updatePatientStatus = $db->updateData('patient_mstr', 
                        ["patient_active = 'N'", "user_updated = '" . $user . "'","date_updated = '$date'"],
                        ["patient_code = '$patientId'", "patient_kk = '$kk'"]);

                if ($updatePatientStatus) {
                    echo '<script>
                        if(!alert("Patient with prescription id ' . $patientId . ' is deleted!"))
                            {window.location.replace("m1min.php");}
                    </script>';
                }

                $patientId = '';
                $patientName = '';
                $medCenter = '';
                $dose = '';
                $volume = '';
                $presDate = $todayDate;

                /*$db->insertData('patient_mstr', 
                    ['patient_code', 'patient_name', 'patient_kk', 'patient_dose', 
                    'patient_volume', 'patient_m1min', 'user_created','date_created'],
                    [$patientId, $patientName, $medCenter, 
                    $dose, $volume, 'Y', 'admin', $date]);*/
        }
    }

?>

<!DOCTYPE html>
<html>
<head>
    <title>MethaSys-M1M In</title>
    <link rel="stylesheet" type="text/css" href="../css/m1mStyle.css">
    <script src="../scripts/jquery-1.11.2.min.js"></script>
    <script>
        $("document").ready(function(){

            $("#patient-supplyfrom").change(function(){
                supplyFrom = $("patient-supplyfrom").val();
                supplyTo = $("patient-supplyto").val();
                date1 = new Date(supplyFrom);
                year = date1.getFullYear();
                $("patient-supplydays").val(year);
                    
            });
        });
    </script>
    <script>
        function getPatientInfo() {

            if (window.XMLHttpRequest) {
                ajaxRequest = new XMLHttpRequest();
            } else {
                ajaxRequest = new ActiveXObject("Microsoft.XMLHTTP");
            }

            ajaxRequest.onreadystatechange = function() {
                if (ajaxRequest.readyState == 4 && ajaxRequest.status == 200) {
                    var jsonObj = JSON.parse(ajaxRequest.responseText);

                    var patientName = jsonObj.patient_name;
                    var medCenter = jsonObj.patient_fromkk;
                    var dose = jsonObj.patient_dose;
                    var volume = jsonObj.patient_volume;

                    document.getElementById("patient-patname").value = jsonObj.patient_name;
                    document.getElementById("patient-medcenter").value = jsonObj.patient_fromkk;
                    document.getElementById("patient-dose").value = jsonObj.patient_dose;
                    document.getElementById("patient-volume").value = jsonObj.patient_volume;

                    if ((patientName != '') && (medCenter != '') && (dose != '') && (volume != '')) {
                        document.getElementById("delete").style.visibility = "visible";
                        document.getElementById("insert").value = 'Proceed';
                        document.getElementById("insert").name = 'proceed'; 
                    } else {
                        document.getElementById("delete").style.visibility = "hidden";
                        document.getElementById("insert").value = 'Save';
                        document.getElementById("insert").name = 'insert';
                    }
                }
            }

            var code = document.getElementById("patient-presid").value;

            ajaxRequest.open("POST", "../scripts/getM1MInCode.php?code="+code, true);
            ajaxRequest.send();
        }

        function getPatientInfoByFullCode() {

            if (window.XMLHttpRequest) {
                ajaxRequest = new XMLHttpRequest();
            } else {
                ajaxRequest = new ActiveXObject("Microsoft.XMLHTTP");
            }

            ajaxRequest.onreadystatechange = function() {
                if (ajaxRequest.readyState == 4 && ajaxRequest.status == 200) {
                    var jsonObj = JSON.parse(ajaxRequest.responseText);

                    var patientCode = jsonObj.patient_code;
                    var patientName = jsonObj.patient_name;
                    var medCenter = jsonObj.patient_fromkk;
                    var dose = jsonObj.patient_dose;
                    var volume = jsonObj.patient_volume;

                    document.getElementById("patient-presid").value = patientCode
                    document.getElementById("patient-patname").value = jsonObj.patient_name;
                    document.getElementById("patient-medcenter").value = jsonObj.patient_fromkk;
                    document.getElementById("patient-dose").value = jsonObj.patient_dose;
                    document.getElementById("patient-volume").value = jsonObj.patient_volume;

                    if ((patientName != '') && (medCenter != '') && (dose != '') && (volume != '')) {
                        document.getElementById("delete").style.visibility = "visible";
                        document.getElementById("insert").value = 'Proceed';
                        document.getElementById("insert").name = 'proceed'; 
                    } else {
                        document.getElementById("delete").style.visibility = "hidden";
                        document.getElementById("insert").value = 'Save';
                        document.getElementById("insert").name = 'insert';
                    }
                }
            }

            var code = document.getElementById("m1m-code").value;

            ajaxRequest.open("POST", "../scripts/getM1MInCode.php?code="+code, true);
            ajaxRequest.send();
        }

        function autoCalVolume() {
            var volume = document.getElementById('patient-volume');
            var dose = document.getElementById('patient-dose');
            volume.value = dose.value/5;
        }

        function autoCalDose() {
            var volume = document.getElementById('patient-volume');
            var dose = document.getElementById('patient-dose');
            dose.value = volume.value*5;
        }
    </script>
</head>
<body>
<div id="header">
    <?php require_once '../includes/titleHeader.php';?>
        <lable>MethaSys - M1M In Page</lable>
    <?php require_once '../includes/titleSubFooter.php';?>
    <?php require_once '../includes/menuHeader.php';?>
        <li><a href="../announcement.php">Annoucement</a></li>
        <li><a href="../scan.php">Scan</a></li>
        <li><a href="../home.php">Log</a></li>
        <li><a href="../spubm1m.php" style="color: #FFFFFF;font-size: 18px;text-decoration: none;">SPUB/M1M</a></li>
        <li><a href="../maintenance.php">Maintenance</a></li>
        <li><a href="../report.php">Report</a></li>
    <?php require_once '../includes/menuFooter.php';?>
</div>

    <div id="content">
        <div id="patient-m1m">
            <form method='post' autocomplete="off">
                <p id="m1m-code-content" style="font-weight:bold;padding-left:20px;">
                    <label>M1M Code :</label>
                    <select id="m1m-code" name="m1m-code" onchange="getPatientInfoByFullCode()">
                        <?php
                            echo '<option></option>';
                            while ($row = $result->fetch_assoc()) {
                                echo '<option value="' . $row['patient_code'] . '">' . $row['patient_code'] . '>>>' . $row['patient_name'] . '</option>';
                            }
                        ?>
                    </select>
                </p> 
                <div id="m1mForm" style="visibility:visible">
                    <table>
                        <tr>
                            <th>Prescription Number</th>
                            <td><input id="patient-presid" name="patient-presid" type="text"  style="text-transform:uppercase;" oninput="getPatientInfo()"></td>    
                            <th>Patient Name</th>
                            <td><input id="patient-patname" name="patient-patname" type="text" style="text-transform:uppercase;"></td>                          
                        </tr>
                        <script>document.getElementById("patient-presid").value = "<?php echo trim($patientId);?>";</script>
                        <script>document.getElementById("patient-patname").value = "<?php echo trim($patientName);?>";</script> 
                        <tr>
                            <td></td>
                            <td>
                                <?php
                                    if (($_SERVER['REQUEST_METHOD'] == 'POST') && in_array('patient-presid', $missing)) {
                                        echo '<span style="color:red">Please fill in patient\'s prescription id</span>';             
                                    }
                                ?>
                            </td>    
                            <td></td>
                            <td>
                                <?php
                                    if (($_SERVER['REQUEST_METHOD'] == 'POST') && in_array('patient-patname', $missing)) {
                                        echo '<span style="color:red">Please fill in patient\'s name</span>';             
                                    }
                                ?>
                            </td>                    
                        </tr>
                        <tr>
                            <th>Medical Center</th>
                            <td><input id="patient-medcenter" name="patient-medcenter" type="text" style="text-transform:uppercase;"></td>  
                            <th>Prescription Date</th>
                            <td><input id="patient-presdate" name="patient-presdate" type="date" value="<?php echo $todayDate;?>"></td>                        
                        </tr>
                        <script>document.getElementById("patient-presdate").value = "<?php echo trim($presDate);?>";</script>
                        <script>document.getElementById("patient-medcenter").value = "<?php echo trim($medCenter);?>";</script> 
                        <tr>
                            <td></td>
                            <td>
                                <?php
                                    if (($_SERVER['REQUEST_METHOD'] == 'POST') && in_array('patient-medcenter', $missing)) {
                                        echo '<span style="color:red">Please fill in medical center</span>';             
                                    }
                                ?>
                            </td>
                            <td></td>
                            <td>
                                <?php
                                    if (($_SERVER['REQUEST_METHOD'] == 'POST') && in_array('patient-presdate', $missing)) {
                                        echo '<span style="color:red">Please fill in patient\'s prescription date</span>';             
                                    }
                                ?>
                            </td>
                        </tr>
                        <tr>     
                            <th>Methadone Dose</th>
                            <td><input type="number" min="5" max="200" step="5" id="patient-dose" name="patient-dose" onchange="autoCalVolume()"></td>  
                            <th>Methadone Volume</th>
                            <td><input type="number" min="1" max="40" step="1" id="patient-volume" name="patient-volume" onchange="autoCalDose()"></td>   
                        </tr>
                        <script>document.getElementById("patient-dose").value = "<?php echo trim($dose);?>";</script>
                        <script>document.getElementById("patient-volume").value = "<?php echo trim($volume);?>";</script>
                        <tr>
                            <td></td>
                            <td>
                                <?php
                                    if (($_SERVER['REQUEST_METHOD'] == 'POST') && in_array('patient-dose', $missing)) {
                                        echo '<span style="color:red">Please fill in patient\'s prescription dose</span>';             
                                    }
                                ?>
                            </td>
                            <td></td>
                            <td>
                                <?php
                                    if (($_SERVER['REQUEST_METHOD'] == 'POST') && in_array('patient-volume', $missing)) {
                                        echo '<span style="color:red">Please fill in patient\'s prescription volume</span>';             
                                    }
                                ?>
                            </td>
                        </tr>
                    </table>
                    <input type="submit" id="insert" name="insert" value="Save">
                    <input type="submit" id="delete" name="delete" value="Delete" style="visibility:hidden;">
                    
                </div>
            </form>          
        </div>    
    </div>  
    <p id="result"></p>   
    <?php require_once '../includes/pageSubFooter.php';?>
</body>
</html>
