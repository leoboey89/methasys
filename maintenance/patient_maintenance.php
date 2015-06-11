<?php
    header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
    header("Cache-Control: no-store, no-cache, must-revalidate");
    header("Pragma: no-cache");

    session_set_cookie_params(0);

    require_once '../classes/Date.php';
    require_once '../classes/MySQLConnector.php';
    require_once '../classes/Validator.php';
    require_once '../includes/session.php';

    $user = $_COOKIE['user'];
    $kk = $_COOKIE['kk'];

    $db = new MySQLConnector('localhost', 'leoboey_db', 'methasys2015', 'leoboey_db');
    $result = $db->getResultSet(['patient_mstr'], ['patient_id', 'patient_code', 
        'patient_name', 'patient_methatype', 'patient_dose', 'patient_volume',
        'patient_age', 'patient_gender', 'patient_status', 'patient_ename', 
        'patient_econtact', 'patient_active', 'user_created', 'user_updated',
        'date_created', 'date_updated'], 
        ['patient_active="Y"', 'patient_spubin = "N"', 'patient_m1min = "N"', 'patient_code not like "SPUB%"', 
        'patient_kk = "' . $kk . '"'], ['patient_id'], ['patient_code']);

    $patientCode = '';
    $patientName = '';
    $patientMethatype = '';
    $patientDose = '';
    $patientVolume = '';
    $patientAge = '';
    $patientGender = '';
    $patientStatus = '';
    $patientEname = '';
    $patientEcontact = '';
    $patientActive = '';
    $patientMobile = '';
    $patientTel = '';
    $patientEmail = '';
    $patientAddr1 = '';
    $patientAddr2 = '';
    $patientAddr3 = '';
    $patientAddr4 = '';
    $patientPostcode = '';
    $patientCity = '';
    $patientState = '';
    $userCreated = '';
    $userUpdated = '';
    $dateCreated = '';
    $dateUpdated = '';
    $missing = array();

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        /*Update patient's info*/
        if (!empty($_POST['update']) && !empty($_POST['patient-code'])) {
            /*Get user entered value from each field*/
            $patientCode = $_POST['patient-code'];
            $patientName = strtoupper($_POST['patient-name']);
            $patientMethatype = strtoupper($_POST['patient-methatype']);
            $patientDose = strtoupper($_POST['patient-dose']);
            $patientVolume = strtoupper($_POST['patient-volume']);
            $patientAge = strtoupper($_POST['patient-age']);
            $patientGender = strtoupper($_POST['patient-gender']);
            $patientStatus = strtoupper($_POST['patient-status']);
            $patientEname = strtoupper($_POST['patient-ename']);
            $patientEcontact = strtoupper($_POST['patient-econtact']);
            $patientActive = strtoupper($_POST['patient-active']);
            $patientMobile = strtoupper($_POST['patient-mobile']);
            $patientTel = strtoupper($_POST['patient-tel']);
            $patientEmail = strtoupper($_POST['patient-email']);
            $patientAddr1 = strtoupper($_POST['patient-addr1']);
            $patientAddr2 = strtoupper($_POST['patient-addr2']);
            $patientAddr3 = strtoupper($_POST['patient-addr3']);
            $patientAddr4 = strtoupper($_POST['patient-addr4']);
            $patientPostcode = strtoupper($_POST['patient-postcode']);
            $patientCity = strtoupper($_POST['patient-city']);
            $patientState = strtoupper($_POST['patient-state']);
            $userCreated = strtoupper($_POST['patient-user-created']);
            $userUpdated = strtoupper($_POST['patient-user-updated']);
            $dateCreated = strtoupper($_POST['patient-date-created']);
            $dateUpdated = strtoupper($_POST['patient-date-updated']);

            /*Create an Validator object to validate input field*/
            $validator = new Validator(['patient-name', 'patient-methatype', 'patient-dose', 
            'patient-volume', 'patient-age', 'patient-gender', 'patient-status']);

            $missingInput = $validator->getMissingInput();
            /*If any compulsary field is not filled up*/
            if (!empty($missingInput)) {

                /*Get missing field from Validator object*/
                $missing = $validator->getMissingInput();
            } else {
                if (trim($_POST['update']) == 'Update') {
                    /*Update patient's info*/
                    date_default_timezone_set('Asia/Kuala_Lumpur');
                    $date = date("Y-m-d H:i:s");
                    $updateSuccess = $db->updateData('patient_mstr', ["patient_name='$patientName'", 
                        "patient_methatype='$patientMethatype'", "patient_dose=$patientDose",
                        "patient_volume=$patientVolume", "patient_age=$patientAge", 
                        "patient_gender='$patientGender'", "patient_status='$patientStatus'",
                        "patient_ename='$patientEname'", "patient_econtact='$patientEcontact'",
                        "patient_active='$patientActive'", "patient_mobile='$patientMobile'",
                        "patient_tel='$patientTel'", "patient_email='$patientEmail'",
                        "patient_addr1='$patientAddr1'", "patient_addr2='$patientAddr2'",
                        "patient_addr3='$patientAddr3'", "patient_addr4='$patientAddr4'",
                        "patient_postcode='$patientPostcode'", "patient_city='$patientCity'",
                        "patient_state='$patientState'", "user_updated='$user'",
                        "date_updated='$date'"], ["patient_code='$patientCode'", "patient_kk = '$kk'"]);

                    /*Prompt update status for user*/
                    if ($updateSuccess) {
                        echo '<script type="text/javascript">alert("' . $patientName . ', ' . $patientCode . ' Successful Updated!");</script>'; 
                        $_POST['patient-code'] = ''; 
                        $patientCode = '';
                        $patientName = '';
                        $patientMethatype = '';
                        $patientDose = '';
                        $patientVolume = '';
                        $patientAge = '';
                        $patientGender = '';
                        $patientStatus = '';
                        $patientEname = '';
                        $patientEcontact = '';
                        $patientActive = '';
                        $patientMobile = '';
                        $patientTel = '';
                        $patientEmail = '';
                        $patientAddr1 = '';
                        $patientAddr2 = '';
                        $patientAddr3 = '';
                        $patientAddr4 = '';
                        $patientPostcode = '';
                        $patientCity = '';
                        $patientState = '';
                        $userCreated = '';
                        $userUpdated = '';
                        $dateCreated = '';
                        $dateUpdated = '';     
                        $result = $db->getResultSet('patient_mstr', ['patient_id', 'patient_code', 
                            'patient_name', 'patient_methatype', 'patient_dose', 'patient_volume',
                            'patient_age', 'patient_gender', 'patient_status', 'patient_ename', 
                            'patient_econtact', 'patient_active'], 
                            ['patient_active="Y"', "patient_kk = '$kk'", 'patient_spubin = "N"', 'patient_m1min = "N"'], ['patient_id'], ['patient_code']);        
                    } else {
                        echo '<script type="text/javascript">alert("Failed to update!");</script>';
                    }
                }
            }
        } else if (!empty($_POST['death']) && !empty($_POST['patient-code'])) {
            /*Get user entered value from each field*/
            $patientCode = $_POST['patient-code'];
            $patientName = strtoupper($_POST['patient-name']);
            $patientMethatype = strtoupper($_POST['patient-methatype']);
            $patientDose = strtoupper($_POST['patient-dose']);
            $patientVolume = strtoupper($_POST['patient-volume']);
            $patientAge = strtoupper($_POST['patient-age']);
            $patientGender = strtoupper($_POST['patient-gender']);
            $patientStatus = strtoupper($_POST['patient-status']);
            $patientEname = strtoupper($_POST['patient-ename']);
            $patientEcontact = strtoupper($_POST['patient-econtact']);
            $patientActive = strtoupper($_POST['patient-active']);
            $patientMobile = strtoupper($_POST['patient-mobile']);
            $patientTel = strtoupper($_POST['patient-tel']);
            $patientEmail = strtoupper($_POST['patient-email']);
            $patientAddr1 = strtoupper($_POST['patient-addr1']);
            $patientAddr2 = strtoupper($_POST['patient-addr2']);
            $patientAddr3 = strtoupper($_POST['patient-addr3']);
            $patientAddr4 = strtoupper($_POST['patient-addr4']);
            $patientPostcode = strtoupper($_POST['patient-postcode']);
            $patientCity = strtoupper($_POST['patient-city']);
            $patientState = strtoupper($_POST['patient-state']);
            $userCreated = strtoupper($_POST['patient-user-created']);
            $userUpdated = strtoupper($_POST['patient-user-updated']);
            $dateCreated = strtoupper($_POST['patient-date-created']);
            $dateUpdated = strtoupper($_POST['patient-date-updated']);

            /*Create an Validator object to validate input field*/
            $validator = new Validator(['patient-name', 'patient-methatype', 'patient-dose', 
            'patient-volume', 'patient-age', 'patient-gender', 'patient-status']);

            $missingInput = $validator->getMissingInput();
            /*If any compulsary field is not filled up*/
            if (!empty($missingInput)) {

                /*Get missing field from Validator object*/
                $missing = $validator->getMissingInput();
            } else {
                if (trim($_POST['death']) == 'Death') {
                    /*Update patient's info*/
                    date_default_timezone_set('Asia/Kuala_Lumpur');
                    $date = date("Y-m-d H:i:s");
                    $updateSuccess = $db->updateData('patient_mstr', ["patient_name='$patientName'", 
                        "patient_methatype='$patientMethatype'", "patient_dose=$patientDose",
                        "patient_volume=$patientVolume", "patient_age=$patientAge", 
                        "patient_gender='$patientGender'", "patient_status='DEATH'",
                        "patient_ename='$patientEname'", "patient_econtact='$patientEcontact'",
                        "patient_active='$patientActive'", "patient_mobile='$patientMobile'",
                        "patient_tel='$patientTel'", "patient_email='$patientEmail'",
                        "patient_addr1='$patientAddr1'", "patient_addr2='$patientAddr2'",
                        "patient_addr3='$patientAddr3'", "patient_addr4='$patientAddr4'",
                        "patient_postcode='$patientPostcode'", "patient_city='$patientCity'",
                        "patient_state='$patientState'", "user_updated='$user'",
                        "date_updated='$date'"], ["patient_code='$patientCode'", "patient_kk = '$kk'"]);
                    /*Insert patient's info into reactivate_mstr*/
                    $deathStatus = $db->insertData('death_mstr',['death_patcode', 
                        'death_patname', 'death_kk', 'user_created', 'date_created'],
                        [$patientCode, $patientName, $kk, $user, $date]);

                    /*Prompt update status for user*/
                    if ($updateSuccess && $deathStatus) {
                        echo '<script type="text/javascript">alert("' . $patientName . ', ' . $patientCode . ' Status Successfully Changed To DEATH.");</script>';
                        $_POST['patient-code'] = ''; 
                        $patientCode = '';
                        $patientName = '';
                        $patientMethatype = '';
                        $patientDose = '';
                        $patientVolume = '';
                        $patientAge = '';
                        $patientGender = '';
                        $patientStatus = '';
                        $patientEname = '';
                        $patientEcontact = '';
                        $patientActive = '';
                        $patientMobile = '';
                        $patientTel = '';
                        $patientEmail = '';
                        $patientAddr1 = '';
                        $patientAddr2 = '';
                        $patientAddr3 = '';
                        $patientAddr4 = '';
                        $patientPostcode = '';
                        $patientCity = '';
                        $patientState = '';
                        $userCreated = '';
                        $userUpdated = '';
                        $dateCreated = '';
                        $dateUpdated = '';     
                        $result = $db->getResultSet('patient_mstr', ['patient_id', 'patient_code', 
                            'patient_name', 'patient_methatype', 'patient_dose', 'patient_volume',
                            'patient_age', 'patient_gender', 'patient_status', 'patient_ename', 
                            'patient_econtact', 'patient_active'], 
                            ['patient_active="Y"', 'patient_kk = "' . $kk . '"', 'patient_spubin = "N"', 'patient_m1min = "N"'], ['patient_id'], ['patient_code']);
                    } else {
                        echo '<script type="text/javascript">alert("Failed to update!");</script>';
                    }
                }
            }
        }
        else if (!empty($_POST['transout']) && !empty($_POST['patient-code'])) {
            /*Get user entered value from each field*/
            $patientCode = $_POST['patient-code'];
            $patientName = strtoupper($_POST['patient-name']);
            $patientMethatype = strtoupper($_POST['patient-methatype']);
            $patientDose = strtoupper($_POST['patient-dose']);
            $patientVolume = strtoupper($_POST['patient-volume']);
            $patientAge = strtoupper($_POST['patient-age']);
            $patientGender = strtoupper($_POST['patient-gender']);
            $patientStatus = strtoupper($_POST['patient-status']);
            $patientEname = strtoupper($_POST['patient-ename']);
            $patientEcontact = strtoupper($_POST['patient-econtact']);
            $patientActive = strtoupper($_POST['patient-active']);
            $patientMobile = strtoupper($_POST['patient-mobile']);
            $patientTel = strtoupper($_POST['patient-tel']);
            $patientEmail = strtoupper($_POST['patient-email']);
            $patientAddr1 = strtoupper($_POST['patient-addr1']);
            $patientAddr2 = strtoupper($_POST['patient-addr2']);
            $patientAddr3 = strtoupper($_POST['patient-addr3']);
            $patientAddr4 = strtoupper($_POST['patient-addr4']);
            $patientPostcode = strtoupper($_POST['patient-postcode']);
            $patientCity = strtoupper($_POST['patient-city']);
            $patientState = strtoupper($_POST['patient-state']);
            $userCreated = strtoupper($_POST['patient-user-created']);
            $userUpdated = strtoupper($_POST['patient-user-updated']);
            $dateCreated = strtoupper($_POST['patient-date-created']);
            $dateUpdated = strtoupper($_POST['patient-date-updated']);

            /*Create an Validator object to validate input field*/
            $validator = new Validator(['patient-name', 'patient-methatype', 'patient-dose', 
            'patient-volume', 'patient-age', 'patient-gender', 'patient-status']);

            $missingInput = $validator->getMissingInput();
            /*If any compulsary field is not filled up*/
            if (!empty($missingInput)) {

                /*Get missing field from Validator object*/
                $missing = $validator->getMissingInput();
            } else {
                if (trim($_POST['transout']) == 'Transfer Out') {
                    /*Update patient's info*/
                    date_default_timezone_set('Asia/Kuala_Lumpur');
                    $date = date("Y-m-d H:i:s");
                    $updateSuccess = $db->updateData('patient_mstr', ["patient_name='$patientName'", 
                        "patient_methatype='$patientMethatype'", "patient_dose=$patientDose",
                        "patient_volume=$patientVolume", "patient_age=$patientAge", 
                        "patient_gender='$patientGender'", "patient_status='TRANSFER OUT'",
                        "patient_ename='$patientEname'", "patient_econtact='$patientEcontact'",
                        "patient_active='$patientActive'", "patient_mobile='$patientMobile'",
                        "patient_tel='$patientTel'", "patient_email='$patientEmail'",
                        "patient_addr1='$patientAddr1'", "patient_addr2='$patientAddr2'",
                        "patient_addr3='$patientAddr3'", "patient_addr4='$patientAddr4'",
                        "patient_postcode='$patientPostcode'", "patient_city='$patientCity'",
                        "patient_state='$patientState'", "user_updated='$user'",
                        "date_updated='$date'"], ["patient_code='$patientCode'", "patient_kk = '$kk'"]);  
                    
                    $transoutStatus = $db->insertData('transout_mstr',['transout_patcode', 
                        'transout_patname', 'transout_kk', 'user_created', 'date_created'],
                        [$patientCode, $patientName, $kk, $user, $date]);

                    /*Prompt update status for user*/
                    if ($updateSuccess && $transoutStatus) {
                        echo '<script type="text/javascript">alert("' . $patientName . ', ' . $patientCode . ' Status Successfully Changed To TRANSFER OUT!");</script>';
                        $_POST['patient-code'] = ''; 
                        $patientCode = '';
                        $patientName = '';
                        $patientMethatype = '';
                        $patientDose = '';
                        $patientVolume = '';
                        $patientAge = '';
                        $patientGender = '';
                        $patientStatus = '';
                        $patientEname = '';
                        $patientEcontact = '';
                        $patientActive = '';
                        $patientMobile = '';
                        $patientTel = '';
                        $patientEmail = '';
                        $patientAddr1 = '';
                        $patientAddr2 = '';
                        $patientAddr3 = '';
                        $patientAddr4 = '';
                        $patientPostcode = '';
                        $patientCity = '';
                        $patientState = '';
                        $userCreated = '';
                        $userUpdated = '';
                        $dateCreated = '';
                        $dateUpdated = '';     
                        $result = $db->getResultSet('patient_mstr', ['patient_id', 'patient_code', 
                            'patient_name', 'patient_methatype', 'patient_dose', 'patient_volume',
                            'patient_age', 'patient_gender', 'patient_status', 'patient_ename', 
                            'patient_econtact', 'patient_active'], 
                            ['patient_active="Y"', 'patient_kk = "' . $kk . '"', 'patient_spubin = "N"', 'patient_m1min = "N"'], ['patient_id'], ['patient_code']);
                              
                    } else {
                        echo '<script type="text/javascript">alert("Failed to update!");</script>';
                    }
                }
            }
        }
        /*Get selected user's data from database*/
        else if (!empty($_POST['patient-code']) && empty($_POST['update'])) {
            $patientResult = $db->getResultSet('patient_mstr', ['patient_code, patient_name, 
                patient_methatype, patient_dose, patient_volume, patient_age,
                patient_gender, patient_status, patient_ename, patient_econtact', 
                'patient_active', 'patient_mobile', 'patient_tel', 'patient_email', 
                'patient_addr1', 'patient_addr2', 'patient_addr3', 'patient_addr4', 
                'patient_postcode', 'patient_city', 'patient_state', 'user_created', 
                'user_updated', 'date_created', 'date_updated'], 
                ['patient_code="' . $_POST['patient-code'] . '"', 'patient_kk = "' . $kk . '"'], ['patient_id'], ['patient_code']);
            if ($patientRow = $patientResult->fetch_assoc()) {
                $patientCode = $patientRow['patient_code'];
                $patientName = $patientRow['patient_name'];
                $patientMethatype = $patientRow['patient_methatype'];
                $patientDose = $patientRow['patient_dose'];
                $patientVolume = $patientRow['patient_volume'];
                $patientAge = $patientRow['patient_age'];
                $patientGender = $patientRow['patient_gender'];
                $patientStatus = $patientRow['patient_status'];
                $patientEname = $patientRow['patient_ename'];
                $patientEcontact = $patientRow['patient_econtact'];
                $patientActive = $patientRow['patient_active'];
                $patientMobile = $patientRow['patient_mobile'];
                $patientTel = $patientRow['patient_tel'];
                $patientEmail = $patientRow['patient_email'];
                $patientAddr1 = $patientRow['patient_addr1'];
                $patientAddr2 = $patientRow['patient_addr2'];
                $patientAddr3 = $patientRow['patient_addr3'];
                $patientAddr4 = $patientRow['patient_addr4'];
                $patientPostcode = $patientRow['patient_postcode'];
                $patientCity = $patientRow['patient_city'];
                $patientState = $patientRow['patient_state'];
                $userCreated = $patientRow['user_created'];
                $userUpdated = $patientRow['user_updated'];
                $dateCreated = $patientRow['date_created'];
                $dateUpdated = $patientRow['date_updated'];
            } else {
                $patientCode = $_POST['patient-code'];
            }
        }
    }
?>

<!DOCTYPE html>
<html>
<head>
    <title>MethaSys-Patient Maintenance</title>
    <link rel="stylesheet" type="text/css" href="../css/mystyle.css">
    <script type="text/javascript">

        function displayPatientInfo() {

            var selectObj = document.getElementById("patient-code");
            var code = selectObj.options[selectObj.selectedIndex].value;

            if (window.XMLHttpRequest) {
                ajaxRequest = new XMLHttpRequest();
            } else {
                ajaxRequest = new ActiveXObject("Microsoft.XMLHTTP");
            }

            ajaxRequest.onreadystatechange = function() {
                if (ajaxRequest.readyState == 4 && ajaxRequest.status == 200) {
                    var jsonObj = JSON.parse(ajaxRequest.responseText);

                    document.getElementById('patient-update-table').style.visibility = 'visible';
                    document.getElementById('patient-name').value = jsonObj.patient_name;
                    document.getElementById('patient-methatype').value = jsonObj.patient_methatype;
                    document.getElementById('patient-dose').value = jsonObj.patient_dose;
                    document.getElementById('patient-volume').value = jsonObj.patient_volume;
                    document.getElementById('patient-age').value = jsonObj.patient_age;
                    document.getElementById('patient-gender').value = jsonObj.patient_gender;
                    document.getElementById('patient-status').value = jsonObj.patient_status;
                    document.getElementById('patient-ename').value = jsonObj.patient_ename;
                    document.getElementById('patient-econtact').value = jsonObj.patient_econtact;
                    document.getElementById('patient-active').value = jsonObj.patient_active;
                    document.getElementById('patient-mobile').value = jsonObj.patient_mobile;
                    document.getElementById('patient-tel').value = jsonObj.patient_tel;
                    document.getElementById('patient-email').value = jsonObj.patient_email;
                    document.getElementById('patient-addr1').value = jsonObj.patient_addr1;
                    document.getElementById('patient-addr2').value = jsonObj.patient_addr2;
                    document.getElementById('patient-addr3').value = jsonObj.patient_addr3;
                    document.getElementById('patient-addr4').value = jsonObj.patient_addr4;
                    document.getElementById('patient-postcode').value = jsonObj.patient_postcode;
                    document.getElementById('patient-city').value = jsonObj.patient_city;
                    document.getElementById('patient-state').value = jsonObj.patient_state;
                    document.getElementById('patient-user-created').innerHTML = jsonObj.user_created;
                    document.getElementById('patient-user-updated').innerHTML = jsonObj.user_updated;
                    document.getElementById('patient-date-created').innerHTML = jsonObj.date_created;
                    document.getElementById('patient-date-updated').innerHTML = jsonObj.date_updated;
                    
                    if (jsonObj.patient_name.trim() != '') {
                        document.getElementById("update").style.background = '#005C00';
                        document.getElementById("update").disabled = false;
                        document.getElementById("patient-update-table").style.visibility = 'visible';
                    } else {
                        document.getElementById("update").style.background = '#B80000';
                        document.getElementById("update").disabled = true;
                        document.getElementById("patient-update-table").style.visibility = 'hidden';
                    }
                } 
            }

            ajaxRequest.open("POST", "scripts/patient_fullinfo_ajax.php?code="+code, true);
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
        <label>MethaSys - Update Patient Page</label>
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
        <div id="patient-maintenance">
            <div id="tabs">
                <form name="updateForm" method="post" autocomplete="off" enctype="multipart/form-data">
                    <label style='font-weight:bold;padding-left:5px;padding-right:90px;'>Patient Code :</label>
                    <select id="patient-code" name="patient-code" style="width:365px;" oninput="this.form.submit()">
                        <?php
                            while ($row = $result->fetch_assoc()) {
                                echo '<option value="' . $row['patient_code'] . '">' . $row['patient_code'] . ' >>> ' . $row['patient_name'] . "</option>";
                            }
                        ?>
                    </select>
                    <script>document.getElementById("patient-code").value = "<?php echo trim($patientCode);?>";</script> 
                    <div id="patient-table-wrapper">
                        <table id="patient-update-table" style="visibility:
                            <?php 
                                if (($_SERVER['REQUEST_METHOD'] == 'POST') && (trim($_POST['patient-code']) != '')) {
                                    echo "visible";
                                } else { 
                                    echo "hidden";
                                }
                            ?>;">
                            <tr>
                                <th>Name</th>
                                <td>
                                    <input type="text" id="patient-name" name="patient-name" class="bigCap">
                                </td>
                                <th>Email</th>
                                <td>
                                    <input type="text" id="patient-email" name="patient-email">
                                </td>
                                <script>document.getElementById("patient-name").value = "<?php echo $patientName;?>"</script>    
                                <script>document.getElementById("patient-email").value = "<?php echo $patientEmail;?>"</script>
                            </tr>
                            <tr id="patient-span">
                                <th></th>
                                <td>
                                    <?php
                                        if (($_SERVER['REQUEST_METHOD'] == 'POST') && in_array('patient-name', $missing)) {
                                            echo '<span style="color:red">Please fill in patient\'s name</span>';             
                                        }
                                    ?>
                                </td>                              
                            </tr>
                            <tr>
                                <th>MethaType</th>
                                <td>
                                    <select id="patient-methatype" name="patient-methatype" class="bigCap">
                                        <?php
                                            $methatypeResult = $db->getResultSet('methatype_mstr', ['methatype_name'], 
                                                ['methatype_active = "Y"'], ['methatype_id'], ['methatype_id']);

                                            while($methatypeRow = $methatypeResult->fetch_assoc()) {
                                                if (trim($methatypeRow['methatype_name']) == trim($patientMethatype)) {
                                                    echo '<option selected="selected" value="' . $methatypeRow['methatype_name'] . '">' . $methatypeRow['methatype_name'] . "</option>";
                                                } else {
                                                    echo '<option value="' . $methatypeRow['methatype_name'] . '">' . $methatypeRow['methatype_name'] . "</option>";
                                                }
                                            }
                                        ?>
                                    </select>
                                </td>
                                <th>Address1</th>
                                <td>
                                    <input type="text" id="patient-addr1" name="patient-addr1" class="bigCap">
                                </td>
                                <script>document.getElementById("patient-addr1").value = "<?php echo $patientAddr1;?>"</script> 
                            </tr>
                            <tr id="patient-span">
                                <th></th>
                                <td>
                                    <?php
                                        if (($_SERVER['REQUEST_METHOD'] == 'POST') && in_array('patient-methatype', $missing)) {
                                            echo '<span style="color:red">Please fill in patient\'s methatype</span>';
                                        }
                                    ?>
                                </td>                                         
                            </tr>
                            <tr>
                                <th>Dose(mg)</th>
                                <td>
                                    <input type="number" min="5" max="200" step="5" id="patient-dose" name="patient-dose" onchange="autoCalVolume()">
                                </td>
                                <th>Address2</th>
                                <td>
                                    <input type="text" id="patient-addr2" name="patient-addr2" class="bigCap">
                                </td>
                                <script>document.getElementById("patient-dose").value = "<?php echo $patientDose;?>"</script> 
                                <script>document.getElementById("patient-addr2").value = "<?php echo $patientAddr2;?>"</script>
                            </tr>
                            <tr id="patient-span">
                                <th></th>
                                <td>
                                    <?php
                                        if (($_SERVER['REQUEST_METHOD'] == 'POST') && in_array('patient-dose', $missing)) {
                                            echo '<span style="color:red">Please fill in patient\'s dose</span>';
                                        }
                                    ?>                                        
                                </td>  
                            </tr>
                            <tr>
                                <th>Volume(ml)</th>
                                <td>
                                    <input type="number" min="1" max="40" id="patient-volume" name="patient-volume" onchange="autoCalDose()">                                        
                                </td>
                                <th>Address3</th>
                                <td>
                                    <input type="text" id="patient-addr3" name="patient-addr3" class="bigCap">
                                </td>
                                <script>document.getElementById("patient-volume").value = "<?php echo $patientVolume;?>"</script>
                                <script>document.getElementById("patient-addr3").value = "<?php echo $patientAddr3;?>"</script>  
                            </tr>
                            <tr id="patient-span">
                                <th></th>
                                <td>
                                    <?php
                                        if (($_SERVER['REQUEST_METHOD'] == 'POST') && in_array('patient-volume', $missing)) {
                                            echo '<span style="color:red">Please fill in patient\'s volume</span>';
                                        }
                                    ?>                                    
                                </td> 
                            </tr>
                            <tr>
                                <th>Age</th>
                                <td>
                                    <select id="patient-age" name="patient-age" class="bigCap">
                                        <?php
                                            for ($i=12; $i <= 100; $i++) { 
                                                if ($i == trim($patientAge)) {
                                                    echo '<option selected="selected" value="' . $i . '">' . $i . "</option>";
                                                } else {
                                                    echo '<option value="' . $i . '">' . $i . "</option>";
                                                }
                                            }
                                        ?>
                                    </select>
                                </td>
                                <th>Address4</th>
                                <td>
                                    <input type="text" id="patient-addr4" name="patient-addr4" class="bigCap">
                                </td>
                                <script>document.getElementById("patient-addr4").value = "<?php echo $patientAddr4;?>"</script>
                            </tr>
                            <tr id="patient-span">
                                <th></th>
                                <td>
                                    <?php
                                        if (($_SERVER['REQUEST_METHOD'] == 'POST') && in_array('patient-age', $missing)) {
                                            echo '<span style="color:red">Please fill in patient\'s age</span>';
                                        }
                                    ?>                                          
                                </td> 
                            </tr>
                            <tr>
                                <th>Gender</th>
                                <td>
                                    <select id="patient-gender" name="patient-gender" class="bigCap">
                                        <?php 
                                            if (trim($patientStatus) == 'MALE') {
                                                echo '<option selected="selected" value="MALE">MALE</option>';
                                                echo '<option value="FEMALE">FEMALE</option>';
                                            } else if (trim($patientStatus) == 'FEMALE'){
                                                echo '<option value="MALE">MALE</option>';
                                                echo '<option selected="selected" value="FEMALE">FEMALE</option>';
                                            } else {
                                                echo '<option value="MALE" selected="selected">MALE</option>';
                                                echo '<option value="FEMALE">FEMALE</option>';
                                            }
                                        ?>
                                    </select>
                                </td>
                                <th>Post Code</th>
                                <td>
                                    <input type="text" id="patient-postcode" name="patient-postcode">
                                </td>
                                <script>document.getElementById("patient-postcode").value = "<?php echo $patientPostcode;?>"</script>
                            </tr>
                            <tr id="patient-span">
                                <th></th>
                                <td>
                                    <?php
                                        if (($_SERVER['REQUEST_METHOD'] == 'POST') && in_array('patient-gender', $missing)) {
                                            echo '<span style="color:red">Please fill in patient\'s gender</span>';
                                        }
                                    ?>                                          
                                </td> 
                            </tr>
                            <tr>
                                <th>Status</th>
                                <td>
                                    <input type="text" id="patient-status" name="patient-status" readonly="readonly">
                                </td>
                                <th>City</th>
                                <td>
                                    <input type="text" id="patient-city" name="patient-city" class="bigCap">
                                </td>
                                <script>document.getElementById("patient-city").value = "<?php echo $patientCity;?>"</script>
                                <script>document.getElementById("patient-status").value = "<?php echo $patientStatus;?>"</script>
                            </tr>
                            <tr>
                                <th>Emg Contact Person</th>
                                <td>
                                    <input type="text" id="patient-ename" name="patient-ename" class="bigCap">                                        
                                </td>
                                <th>State</th>
                                <td>
                                    <input type="text" id="patient-state" name="patient-state" class="bigCap">
                                </td>
                                <script>document.getElementById("patient-ename").value = "<?php echo $patientEname;?>"</script>
                                <script>document.getElementById("patient-state").value = "<?php echo $patientState;?>"</script>
                            </tr>
                            <tr id="patient-span">
                                <th></th>
                                <td>
                                    <?php
                                        if (($_SERVER['REQUEST_METHOD'] == 'POST') && in_array('patient-ename', $missing)) {
                                            echo '<span style="color:red">Please fill in patient\'s emergency contact person</span>';
                                        }
                                    ?>                                          
                                </td> 
                            </tr>
                            <tr>
                                <th>Emg Contact Number</th>
                                <td>
                                    <input type="text" id="patient-econtact" name="patient-econtact">                                        
                                </td>
                                <th>User Created</th>
                                <td>            
                                    <input type="text" id="patient-user-created" name="patient-user-created" readonly="readonly">
                                </td>
                                <script>document.getElementById("patient-econtact").value = "<?php echo $patientEcontact;?>"</script>
                                <script>document.getElementById("patient-user-created").value = "<?php echo $userCreated;?>"</script> 
                            </tr>
                            <tr id="patient-span">
                                <th></th>
                                <td>
                                    <?php
                                        if (($_SERVER['REQUEST_METHOD'] == 'POST') && in_array('patient-econtact', $missing)) {
                                            echo '<span style="color:red">Please fill in patient\'s emergency contact number</span>';
                                        }
                                    ?>                                          
                                </td>
                            </tr>
                            <tr>
                                <th>Active</th>
                                <td>
                                    <select id="patient-active" name="patient-active">
                                        <?php if (trim($patientActive) == 'Y') {
                                                echo '<option selected="selected" value="Y">Y</option>';
                                                echo '<option value="N">N</option>';
                                            } else if (trim($patientActive) == 'N'){
                                                echo '<option value="Y">Y</option>';
                                                echo '<option selected="selected" value="N">N</option>';
                                            } else {
                                                echo '<option value="Y">Y</option>';
                                                echo '<option value="N">N</option>';
                                            }
                                        ?>
                                    </select>
                                    <span style="color:red">Active status does not change patient's status</span>
                                </td>
                                <th>User Updated</th>
                                <td>
                                    <input type="text" id="patient-user-updated" name="patient-user-updated" readonly="readonly">
                                </td>
                                <script>document.getElementById("patient-user-updated").value = "<?php echo $userUpdated;?>"</script> 
                            </tr>
                            <tr>
                                <th>Mobile Number</th>
                                <td>
                                    <input type="text" id="patient-mobile" name="patient-mobile">
                                </td>
                                <th>Date Created</th>
                                <td>
                                    <input type="text" id="patient-date-created" name="patient-date-created" readonly="readonly">
                                </td>
                                <script>document.getElementById("patient-mobile").value = "<?php echo $patientMobile;?>"</script>
                                <script>document.getElementById("patient-date-created").value = "<?php echo $dateCreated;?>"</script> 
                            </tr>
                            <tr>
                                <th>Telephone Number</th>
                                <td>
                                    <input type="text" id="patient-tel" name="patient-tel">
                                </td>
                                <th>Date Updated</th>
                                <td>
                                    <input type="text" id="patient-date-updated" name="patient-date-updated" readonly="readonly">
                                </td>
                                <script>document.getElementById("patient-tel").value = "<?php echo $patientTel;?>"</script>
                                <script>document.getElementById("patient-date-updated").value = "<?php echo $dateUpdated;?>"</script> 
                            </tr>
                        </table>
                        <br>
                        <input type="submit" id="update" name="update" value="Update" style="visibility:
                            <?php if (($_SERVER['REQUEST_METHOD'] == 'POST') && (trim($_POST['patient-code']) != '')
                                && (trim($patientStatus) != 'DEATH') && (trim($patientStatus) != 'TRANSFER OUT')) {
                                    echo "visible";
                                } else { 
                                    echo "hidden";
                                }
                            ?>;">
                        <input type="submit" id="death" name="death" value="Death" style="visibility:
                            <?php if (($_SERVER['REQUEST_METHOD'] == 'POST') && (trim($_POST['patient-code']) != '') 
                                && (trim($patientStatus) != 'DEATH') && (trim($patientStatus) != 'TRANSFER OUT')) {
                                    echo "visible";
                                } else { 
                                    echo "hidden";
                                }
                            ?>;">
                        <input type="submit" id="transout" name="transout" value="Transfer Out" style="visibility:
                            <?php if (($_SERVER['REQUEST_METHOD'] == 'POST') && (trim($_POST['patient-code']) != '')
                                && (trim($patientStatus) != 'DEATH') && (trim($patientStatus) != 'TRANSFER OUT')) {
                                    echo "visible";
                                } else { 
                                    echo "hidden";
                                }
                            ?>;">
                    </div>
                </form>
            </div>
        </div>    
    </div>     
    <?php require_once '../includes/pageSubFooter.php';?>
    <script>
        document.getElementById("patient-name").focus();
    </script>
</body>
</html>
