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
    $m1mCode = '';
    $presId = '';
    $patientCode = '';
    $patientName = '';
    $dose = '';
    $volume = '';
    $dateOut = $todayDate;
    $dateIn = $todayDate;
    $doseRefer = '1';
    $doseLeft = '0';
    $remarks = '';
    $missing = array();
    $kk = $_COOKIE['kk'];
    $user = $_COOKIE['user'];

    /*Get all m1m Out patient for update purpose*/
    $m1mPatientResult = $db->getResultSet('patient_mstr', ['patient_code', 'patient_name'], 
        ['patient_status = "M1M OUT"', 'patient_active = "Y"', 
        'patient_m1mout = "Y"', 'patient_kk = "' . $kk . '"']);
    /*For create new m1m Out for patient, only allow patient with normal status to be selected*/
    $patientResult = $db->getResultSet('patient_mstr', ['patient_code', 'patient_name'], 
        ['patient_status = "NORMAL"', 'patient_active = "Y"', 
        'patient_m1mout = "N"', 'patient_m1min = "N"',
        'patient_spubin = "N"', 'patient_spubout = "N"', 
        'patient_kk = "' . $kk . '"']);

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        /*If Save button is pressed*/
        if (!empty($_POST['insert']) && !empty($_POST['m1m-code'])) {
            /*Get patient's info from field*/
            $m1mCode = $_POST['m1m-code'];
            $presId = strtoupper($_POST['patient-presid']);
            $patientCode = strtoupper($_POST['patient-code']);
            $patientName = strtoupper($_POST['patient-patname']);
            $dose = strtoupper($_POST['patient-dose']);
            $volume = strtoupper($_POST['patient-volume']);
            $dateOut = strtoupper($_POST['patient-dateout']);
            $dateIn = strtoupper($_POST['patient-datein']);
            $doseRefer = strtoupper($_POST['patient-doserefer']);
            $doseLeft = strtoupper($_POST['patient-doseleft']);
            $remarks = $_POST['patient-remarks'];

            /*Create an Validator object to validate input field*/
            $validator = new Validator(['patient-presid', 'patient-code', 'patient-patname', 
                'patient-dose', 'patient-volume', 'patient-dateout', 'patient-datein', 
                'patient-doserefer']);

            $missing = $validator->getMissingInput();
            /*If any compulsary field is not filled up*/
            if (!empty($missing)) {

                /*Get missing field from Validator object*/
                $missing = $validator->getMissingInput();
            } else {
                /*Get current date and time*/
                date_default_timezone_set('Asia/Kuala_Lumpur');
                $date = date("Y-m-d H:i:s");

                /*Insert into spubout_mstr*/
                $insertStatus = $db->insertData('m1mout_mstr',
                        ['m1mout_patcode', 'm1mout_patname', 'm1mout_presid', 'm1mout_dose', 
                        'm1mout_volume', 'm1mout_dateout', 'm1mout_datein', 'm1mout_doserefer', 
                        'm1mout_doseleft', 'm1mout_remarks', 'm1mout_kk', 'user_created', 
                        'date_created'],
                        [$patientCode, $patientName, $presId, $dose, 
                        $volume, $dateOut, $dateIn, $doseRefer, 
                        $doseLeft, $remarks, $kk, $user, 
                        $date]);

                /*Update patient status to SPUB OUT*/
                $updateStatus = $db->updateData('patient_mstr',['patient_status = "M1M OUT"', 'patient_m1mout = "Y"', 
                    'user_updated = "' . $user . '"', 'date_updated = "' . $date . '"'], 
                    ['patient_code = "' . $patientCode . '"', 'patient_kk = "' . $kk . '"']);


                /*Display status of creating SPUB for patient*/
                if ($insertStatus && $updateStatus) {
                    echo '<script type="text/javascript">alert("' . $patientName . ' is now M1M Out")</script>';
                } else {
                    echo '<script type="text/javascript">alert("Failed to create M1M Out for ' . $patientName . '")</script>';
                }

                /*Get all m1m Out patient for update purpose*/
                $m1mPatientResult = $db->getResultSet('patient_mstr', ['patient_code', 'patient_name'], 
                    ['patient_status = "M1M OUT"', 'patient_active = "Y"', 
                    'patient_m1mout = "Y"', 'patient_kk = "' . $kk . '"']);
                /*For create new SPUB Out for patient, only allow patient with normal status to be selected*/
                $patientResult = $db->getResultSet('patient_mstr', ['patient_code', 'patient_name'], 
                    ['patient_status = "NORMAL"', 'patient_active = "Y"', 
                    'patient_m1mout = "N"', 'patient_m1min = "N"',
                    'patient_spubin = "N"', 'patient_spubout = "N"', 
                    'patient_kk = "' . $kk . '"']);


                /*Restructure page*/
                $m1mCode = '';
            } 
        }
         /*If Update button is pressed*/
        if (!empty($_POST['update']) && !empty($_POST['m1m-code'])) {
            /*Get patient's info from field*/

            $m1mCode = $_POST['m1m-code'];
            $presId = strtoupper($_POST['patient-presid']);
            $patientName = strtoupper($_POST['patient-patname']);
            $dose = strtoupper($_POST['patient-dose']);
            $volume = strtoupper($_POST['patient-volume']);
            $dateOut = strtoupper($_POST['patient-dateout']);
            $dateIn = strtoupper($_POST['patient-datein']);
            $doseRefer = strtoupper($_POST['patient-doserefer']);
            $doseLeft = strtoupper($_POST['patient-doseleft']);
            $remarks = $_POST['patient-remarks'];

            /*Create an Validator object to validate input field*/
            $validator = new Validator(['patient-presid', 'patient-patname', 
                'patient-dose', 'patient-volume', 'patient-dateout', 'patient-datein', 
                'patient-doserefer']);

            $missing = $validator->getMissingInput();
            /*If any compulsary field is not filled up*/
            if (!empty($missing)) {

                /*Get missing field from Validator object*/
                $missing = $validator->getMissingInput();
            } else {
                /*Get current date and time*/
                date_default_timezone_set('Asia/Kuala_Lumpur');
                $date = date("Y-m-d H:i:s");

                $latestPatientCodeId = $db->getResultSet('m1mout_mstr', 
                    ['max(m1mout_id) as m1mout_id'], 
                    ['m1mout_patcode = "' . $m1mCode . '"', 'm1mout_active = "Y"',
                    'm1mout_kk = "' . $kk . '"']);

                if ($row = $latestPatientCodeId->fetch_assoc()) {        
                    /*Update SPUB OUT info of patient*/
                    $updateStatus = $db->updateData('m1mout_mstr',
                            ["m1mout_patname='$patientName'", 
                            "m1mout_presid='$presId'", "m1mout_dose='$dose'", 
                            "m1mout_volume='$volume'", "m1mout_dateout='$dateOut'", 
                            "m1mout_datein='$dateIn'", "m1mout_doserefer='$doseRefer'", 
                            "m1mout_doseleft='$doseLeft'", "m1mout_remarks='$remarks'", 
                            "user_updated='" . $user . "'", "date_updated='$date'"],
                            ['m1mout_id = "' . $row['m1mout_id'] . '"', 'm1mout_kk = "' . $kk . '"']);
                };

                /*Display status of creating SPUB for patient*/
                if ($updateStatus) {
                    echo '<script type="text/javascript">alert("' . $patientName . ' is updated")</script>';
                } else {
                    echo '<script type="text/javascript">alert("Failed to create M1M Out for ' . $patientName . '")</script>';
                }

                /*Get all m1m Out patient for update purpose*/
                $m1mPatientResult = $db->getResultSet('patient_mstr', ['patient_code', 'patient_name'], 
                    ['patient_status = "M1M OUT"', 'patient_active = "Y"', 
                    'patient_m1mout = "Y"', 'patient_kk = "' . $kk . '"']);
                /*For create new SPUB Out for patient, only allow patient with normal status to be selected*/
                $patientResult = $db->getResultSet('patient_mstr', ['patient_code', 'patient_name'], 
                    ['patient_status = "NORMAL"', 'patient_active = "Y"', 
                    'patient_m1mout = "N"', 'patient_m1min = "N"',
                    'patient_spubin = "N"', 'patient_spubout = "N"', 
                    'patient_kk = "' . $kk . '"']);

                /*Restructure page*/
                $m1mCode = '';
            } 
        }
        /*If Return button pressed, change back patient's status to normal*/
        else if (!empty($_POST['return']) && !empty($_POST['m1m-code'])){
            /*Get current date and time*/
            date_default_timezone_set('Asia/Kuala_Lumpur');

            $m1mCode = $_POST['m1m-code'];
            $time = date('Y-m-d H:i:s');

            $patientName = strtoupper($_POST['patient-patname']);

            /*Update patient's status to NORMAL*/
            // $spubCode = $_POST['m1m-code'];
            $latestM1MId = $db->getResultSet('m1mout_mstr', 
                ['max(m1mout_id) as m1mout_id'], 
                ['m1mout_patcode = "' . $m1mCode . '"', 'm1mout_active = "Y"', 
                'm1mout_dateout <= "' . $todayDate . '"', 'm1mout_datein >= "' . $todayDate . '"',
                'm1mout_kk = "' . $kk . '"']);
            if ($row = $latestM1MId->fetch_assoc()) {
                $updateM1MStatus = $db->updateData('m1mout_mstr', ['m1mout_datein = "' . $todayDate . '"'], 
                    ['m1mout_id = "' . $row['m1mout_id'] . '"', 'm1mout_dateout <= "' . $todayDate . '"', 
                    'm1mout_datein >= "' . $todayDate . '"', 'm1mout_kk = "' . $kk . '"']);
            }

            $updateStatus = $db->updateData('patient_mstr',['patient_status = "NORMAL"', 'patient_m1mout = "N"', 
                'patient_lastreactivate = "' . $time . '"', 'user_updated = "' . $user . '"', 'date_updated = "' . $date . '"'], 
                    ["patient_code = '$m1mCode'", "patient_kk = '$kk'"]);

            if ($updateStatus && $updateM1MStatus) {
                echo '<script type="text/javascript">alert("' . $patientName . ' returned, patient allows to take methadone.")</script>';
            } 

            /*Get all m1m Out patient for update purpose*/
            $m1mPatientResult = $db->getResultSet('patient_mstr', ['patient_code', 'patient_name'], 
                ['patient_status = "M1M OUT"', 'patient_active = "Y"', 
                'patient_m1mout = "Y"', 'patient_kk = "' . $kk . '"']);
            /*For create new SPUB Out for patient, only allow patient with normal status to be selected*/
            $patientResult = $db->getResultSet('patient_mstr', ['patient_code', 'patient_name'], 
                ['patient_status = "NORMAL"', 'patient_active = "Y"', 
                'patient_m1mout = "N"', 'patient_m1min = "N"',
                'patient_spubin = "N"', 'patient_spubout = "N"', 
                'patient_kk = "' . $kk . '"']);

            /*Restructure page*/
            $m1mCode = '';
        }
        /*If Delete button pressed, change back patient's status to normal*/
        else if (!empty($_POST['delete']) && !empty($_POST['m1m-code'])){
            /*Get current date and time*/
            date_default_timezone_set('Asia/Kuala_Lumpur');
            $time = date('Y-m-d H:i:s');

            $patientName = strtoupper($_POST['patient-patname']);

            /*Update patient's status to NORMAL*/
            $m1mCode = $_POST['m1m-code'];
            $updateStatus = $db->updateData('patient_mstr',['patient_status = "NORMAL"', 'patient_m1mout = "N"', 
                'patient_lastreactivate = "' . $time . '"', 'user_updated = "admin"', 'date_updated = "' . $date . '"'], 
                    ["patient_code = '$m1mCode'", "patient_kk = '$kk'"]);
            $latestM1MId = $db->getResultSet('m1mout_mstr', 
                ['max(m1mout_id) as m1mout_id'], 
                ['m1mout_patcode = "' . $m1mCode . '"', 'm1mout_active = "Y"', 
                'm1mout_dateout <= "' . $todayDate . '"', 'm1mout_datein >= "' . $todayDate . '"',
                'm1mout_kk = "' . $kk . '"']);
            if ($row = $latestM1MId->fetch_assoc()) {
                $updateM1MStatus = $db->updateData('m1mout_mstr', ['m1mout_active = "N"', 'user_updated = "' . $user . '"', 'date_updated = "' . $date . '"'], 
                ['m1mout_id = "' . $row['m1mout_id'] . '"', 'm1mout_kk = "' . $kk . '"']);                
            }

            if ($updateStatus && $updateM1MStatus) {
                echo '<script type="text/javascript">alert("' . $patientName . ' in M1M OUT deleted.")</script>';
            } 

            /*Get all m1m Out patient for update purpose*/
            $m1mPatientResult = $db->getResultSet('patient_mstr', ['patient_code', 'patient_name'], 
                ['patient_status = "M1M OUT"', 'patient_active = "Y"', 
                'patient_m1mout = "Y"', 'patient_kk = "' . $kk . '"']);
            /*For create new SPUB Out for patient, only allow patient with normal status to be selected*/
            $patientResult = $db->getResultSet('patient_mstr', ['patient_code', 'patient_name'], 
                ['patient_status = "NORMAL"', 'patient_active = "Y"', 
                'patient_m1mout = "N"', 'patient_m1min = "N"',
                'patient_spubin = "N"', 'patient_spubout = "N"', 
                'patient_kk = "' . $kk . '"']);

            /*Restructure page*/
            $m1mCode = '';
        }
        else if (!empty($_POST['m1m-code'])) {
            if (($_POST['m1m-code'] == 'Create New') && !empty($_POST['patient-code'])) {
                $m1mCode = $_POST['m1m-code'];
                $patientCode = $_POST['patient-code'];
                $presId = '';
                $patientName = '';
                $dose = '';
                $volume = '';
                $dateOut = $todayDate;
                $missing = array();

                /*Get selected patient's default info*/
                $patientResult = $db->getResultSet('patient_mstr', 
                    ['patient_name', 'patient_age', 'patient_gender', 'patient_tel', 
                    'patient_dose', 'patient_volume'], 
                    ['patient_code = "' . $patientCode . '"', 'patient_kk = "' . $kk . '"']);
                if ($row = $patientResult->fetch_assoc()){
                    $patientName = $row['patient_name'];
                    $patientAge = $row['patient_age'];
                    $patientGender = $row['patient_gender'];
                    $patientContact = $row['patient_tel'];
                    $dose = $row['patient_dose'];
                    $volume = $row['patient_volume'];
                }

                /*Get all m1m Out patient for update purpose*/
                $m1mPatientResult = $db->getResultSet('patient_mstr', ['patient_code', 'patient_name'], 
                    ['patient_status = "M1M OUT"', 'patient_active = "Y"', 
                    'patient_m1mout = "Y"', 'patient_kk = "' . $kk . '"']);
                $patientResult = $db->getResultSet('patient_mstr', ['patient_code', 'patient_name'], 
                    ['patient_status = "NORMAL"', 'patient_active = "Y"', 
                    'patient_m1mout = "N"', 'patient_m1min = "N"',
                    'patient_spubin = "N"', 'patient_spubout = "N"', 
                    'patient_kk = "' . $kk . '"']);

            } else {
                /*Get selected m1m Out patient's code*/
                $m1mCode = $_POST['m1m-code'];

                $latestPatientCodeId = $db->getResultSet('m1mout_mstr', 
                    ['max(m1mout_id) as m1mout_id'], 
                    ['m1mout_patcode = "' . $m1mCode . '"', 'm1mout_active = "Y"', 'm1mout_kk = "' . $kk . '"']);
                if ($row = $latestPatientCodeId->fetch_assoc()){

                    $m1mPatientResult = $db->getResultSet('m1mout_mstr',
                        ['m1mout_id', 'm1mout_patcode', 'm1mout_patname', 'm1mout_presid', 
                        'm1mout_dose', 'm1mout_volume', 'm1mout_dateout', 'm1mout_datein', 
                        'm1mout_doserefer', 'm1mout_doseleft', 'm1mout_remarks', 'm1mout_active'], 
                        ['m1mout_id = "' . $row['m1mout_id'] . '"', 'm1mout_kk = "' . $kk . '"']);
                }

                if ($row = $m1mPatientResult->fetch_assoc()) {
                    $m1mCode = $row['m1mout_patcode'];
                    $presId = $row['m1mout_presid'];
                    $patientCode = $row['m1mout_patcode'];
                    $patientName = $row['m1mout_patname'];
                    $dose = $row['m1mout_dose'];
                    $volume = $row['m1mout_volume'];
                    $dateOut = substr($row['m1mout_dateout'], 0, 10);
                    $dateIn = substr($row['m1mout_datein'], 0, 10);
                    $doseRefer = $row['m1mout_doserefer'];
                    $doseLeft = $row['m1mout_doseleft'];
                    $remarks = $row['m1mout_remarks'];
                }        

                /*Get all m1m Out patient for update purpose*/
                $m1mPatientResult = $db->getResultSet('patient_mstr', ['patient_code', 'patient_name'], 
                    ['patient_status = "M1M OUT"', 'patient_active = "Y"', 
                    'patient_m1mout = "Y"', 'patient_kk = "' . $kk . '"']);
            }
        }
    }
    

?>

<!DOCTYPE html>
<html>
<head>
    <title>MethaSys-M1M Out</title>
    <link rel="stylesheet" type="text/css" href="../css/m1mOutStyle.css">
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
                    var dose = jsonObj.patient_dose;
                    var volume = jsonObj.patient_volume;

                    document.getElementById("patient-patname").value = jsonObj.patient_name;
                    document.getElementById("patient-datein").value = jsonObj.patient_kk;
                    document.getElementById("patient-dose").value = jsonObj.patient_dose;
                    document.getElementById("patient-volume").value = jsonObj.patient_volume;

                    if ((patientName != '') && (dose != '') && (volume != '')) {
                        document.getElementById("delete").style.visibility = "visible";
                        document.getElementById("insert").value = 'Proceed';
                        document.getElementById("insert").name = 'proceed'; 
                    } else {
                        document.getElementById("delete").style.visibility = "hidden";
                        document.getElementById("insert").value = 'Insert';
                        document.getElementById("insert").name = 'insert';
                    }
                }
            }

            var code = document.getElementById("patient-presid").value;

            ajaxRequest.open("POST", "../scripts/getM1MOutCode.php?code="+code, true);
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
                    var dose = jsonObj.patient_dose;
                    var volume = jsonObj.patient_volume;

                    document.getElementById("patient-presid").value = patientCode
                    document.getElementById("patient-patname").value = jsonObj.patient_name;
                    document.getElementById("patient-datein").value = jsonObj.patient_kk;
                    document.getElementById("patient-dose").value = jsonObj.patient_dose;
                    document.getElementById("patient-volume").value = jsonObj.patient_volume;

                    if ((patientName != '') && (dose != '') && (volume != '')) {
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

            ajaxRequest.open("POST", "../scripts/getM1MOutCode.php?code="+code, true);
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
        <lable>MethaSys - M1M Out Page</lable>
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
                    <label>M1M Out :</label>
                    <select id="m1m-code" name="m1m-code" onchange="this.form.submit()">                        
                        <option></option>;
                        <option>Create New</option>;
                        <?php
                            while ($row = $m1mPatientResult->fetch_assoc()) {
                                echo '<option value="' . $row['patient_code'] . '">' . $row['patient_code'] . ' >>> ' . $row['patient_name'] . '</option>';
                            }
                        ?>
                    </select>
                    <script>document.getElementById("m1m-code").value = "<?php echo trim($m1mCode);?>";</script> 
                    <div id="patient-code-content"style="visibility:
                        <?php if ($m1mCode == 'Create New') {
                                echo 'visible';
                            } else {
                                echo 'hidden';
                            };
                        ?>">
                        <label id="patient-code-label">Patient Code :</label>
                        <select id="patient-code" name="patient-code" onchange="this.form.submit()">
                            <option></option>
                            <?php
                                while($row = $patientResult->fetch_assoc()) {
                                    echo '<option value="' . $row['patient_code'] . '">' . $row['patient_code'] . ' >>> ' . $row['patient_name'] . '</option>';
                                }
                            ?>
                        </select>
                        <?php
                            if (($_SERVER['REQUEST_METHOD'] == 'POST') && in_array('patient-code', $missing)) {
                                echo '<span style="color:red">Please fill in patient code</span>';             
                            }
                        ?>
                        <script>document.getElementById("patient-code").value = "<?php echo trim($patientCode);?>";</script>
                    </div>
                </p> 
                <div id="m1mForm" style="visibility:
                    <?php 
                        if ($m1mCode == '') {
                            echo "hidden";
                        } else { 
                            echo "visible";
                        }
                    ?>;">
                    <table>
                        <tr>
                            <th>Prescription Number</th>
                            <td><input id="patient-presid" name="patient-presid" type="text"  style="text-transform:uppercase;"></td>    
                            <th>Patient Name</th>
                            <td><input id="patient-patname" name="patient-patname" type="text" style="text-transform:uppercase;"></td>                          
                        </tr>
                        <script>document.getElementById("patient-presid").value = "<?php echo trim($presId);?>";</script>
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
                        <tr>
                            <th>Date Out</th>
                            <td><input id="patient-dateout" name="patient-dateout" type="date" value="<?php echo $todayDate;?>"></td>    
                            <th>Date In</th>
                            <td><input id="patient-datein" name="patient-datein" type="date" value="<?php echo $todayDate;?>"></td>
                        </tr>
                        <script>document.getElementById("patient-dateout").value = "<?php echo trim($dateOut);?>";</script>
                        <script>document.getElementById("patient-datein").value = "<?php echo trim($dateIn);?>";</script> 
                        <tr>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                        </tr>
                        <tr>
                            <th>Reference Dose</th>
                            <td><input id="patient-doserefer" name="patient-doserefer" type="number" min="1" max="7"></td>    
                            <th>Dose Left</th>
                            <td><input id="patient-doseleft" name="patient-doseleft" type="number" min="0" max="7"></td>
                        </tr>
                        <script>document.getElementById("patient-doserefer").value = "<?php echo trim($doseRefer);?>";</script>
                        <script>document.getElementById("patient-doseleft").value = "<?php echo trim($doseLeft);?>";</script> 
                        <tr>
                            <td></td>
                            <td>
                                <?php
                                    if (($_SERVER['REQUEST_METHOD'] == 'POST') && in_array('patient-doserefer', $missing)) {
                                        echo '<span style="color:red">Please fill in reference dose</span>';             
                                    }
                                ?>
                            </td>
                            <td></td>
                            <td></td>
                        </tr>
                        <tr>
                            <th>Remarks</th>
                            <td><input id="patient-remarks" name="patient-remarks" type="text"></td>    
                            <th></th>
                            <td></td>
                        </tr>
                        <script>document.getElementById("patient-remarks").value = "";</script>
                    </table>
                    <?php
                        if ($m1mCode == 'Create New') {
                            echo '<input type="submit" id="insert" name="insert" value="Save">';
                        } else {
                            echo '<input type="submit" id="update" name="update" value="Update">';
                            echo '<input type="submit" id="return" name="return" value="Return">';
                            echo '<input type="submit" id="delete" name="delete" value="Delete">';
                        }
                    ?>
                </div>
            </form>          
        </div>    
    </div>  
    <p id="result"></p>   
    <?php require_once '../includes/pageSubFooter.php';?>
</body>
</html>
