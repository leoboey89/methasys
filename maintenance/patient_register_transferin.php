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

    /*Get status type from database*/
    $result = $db->getResultSet('status_mstr', ['status_id', 'status_name', 
        'status_type', 'status_active', 'user_created', 'date_created'], 
        ['status_type="CREATE"'], ['status_id'], ['status_id']);

    $statusType = '';
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
        /*Create patient's info*/
        if (!empty($_POST['create']) && !empty($_POST['status-type'])) {
            /*Get user entered value from each field*/
            $checkExistence = false;
            $statusType = $_POST['status-type'];
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

            /*Check existence of entered patient name*/
            $checkExistence = $db->isExist('patient_mstr',['patient_name="' . $patientName . '"','patient_status <> "TRANSFER OUT"', 'patient_kk = "' . $kk . '"']);

            /*If record existed*/
            if ($checkExistence) {
                /*Alert user record not saved*/
                echo '<script type="text/javascript">alert("Failed to create patient, ' . $patientName . '! Record existed!");</script>';
                $_POST['status-type'] = '';
                $statusType = '';
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
            }
            else {
                /*Create an Validator object to validate input field*/
                $validator = new Validator(['patient-name', 'patient-methatype', 'patient-dose', 
                'patient-volume', 'patient-age', 'patient-gender', 'patient-status']);

                $missingInput = $validator->getMissingInput();
                /*If any compulsary field is not filled up*/
                if (!empty($missingInput)) {

                    /*Get missing field from Validator object*/
                    $missing = $validator->getMissingInput();
                } else {
                    if (trim($_POST['create']) == 'Register') {
                        date_default_timezone_set('Asia/Kuala_Lumpur');
                        $date = date("Y-m-d H:i:s");
                        // Create a new patient code
                        // $newCodeResult = $db->getResultSet('patient_mstr', ['max(patient_code)'], ['patient_code not like "SPUB%"']);
                        // $newCodeRow = $newCodeResult->fetch_row();
                        // $newCodeInt = (int)(substr($newCodeRow[0], 2, 5) + 1);
                        // $format = 'SP%1$05d';
                        // $newCode = sprintf($format, $newCodeInt);

                        // Create a new patient code based on prefix of kk respectively
                        // Get prefix of kk based on user logged in
                        $prefixResult = $db->getResultSet(['user_mstr', 'gendoc_mstr'], ['gendoc_prefix'], ['user_name = "' . $user . '"', 'user_active = "Y"', 'user_kk = gendoc_kk']);
                        $prefixRow = $prefixResult->fetch_row();
                        $prefix = $prefixRow[0];

                        // Get max number of patient
                        $maxPatCodeResult = $db->getResultSet('patient_mstr', ['max(patient_code)'], ['patient_spubin = "N"', 'patient_m1min = "N"', 'patient_code like "' . $prefix . '%"']);
                        $maxPatCodeRow = $maxPatCodeResult->fetch_row();
                        if (empty($maxPatCodeRow[0])) {
                            $maxPatCode = '000000';
                        } else {
                            $maxPatCode = $maxPatCodeRow[0];    
                        }

                        // Generate patient with own kk prefix
                        $newCodeInt = (int)(substr($maxPatCode, strlen($maxPatCode) - 5) + 1);
                        $format = $prefix . '%1$05d';
                        $newCode = sprintf($format, $newCodeInt);
                        
                        $insertStatus = $db->insertData('patient_mstr',['patient_code', 'patient_name', 
                            'patient_methatype', 'patient_dose', 'patient_volume', 'patient_age', 
                            'patient_gender', 'patient_status', 'patient_ename', 'patient_econtact', 
                            'patient_active', 'patient_mobile', 'patient_tel', 'patient_email', 
                            'patient_addr1', 'patient_addr2', 'patient_addr3', 'patient_addr4', 
                            'patient_postcode', 'patient_city', 'patient_state', 'patient_kk',
                            'user_created', 'date_created'],
                            [$newCode, trim($patientName), $patientMethatype, $patientDose, 
                            $patientVolume, $patientAge, $patientGender, $patientStatus, 
                            $patientEname, $patientEcontact, $patientActive, $patientMobile, 
                            $patientTel, $patientEmail, $patientAddr1, $patientAddr2, 
                            $patientAddr3, $patientAddr4, $patientPostcode, $patientCity, 
                            $patientState, $kk, $user, $date]);
                        $registerStatus = $db->insertData('register_mstr',['register_patcode', 
                            'register_patname', 'register_kk', 'user_created', 'date_created'],
                            [$newCode, $patientName, $kk, $user, $date]);
                        if ($insertStatus && $registerStatus) {
                            echo '<script type="text/javascript">alert("New registered patient ' . $patientName . ' with patient code, ' . $newCode . ' successfully created!");</script>';
                            $_POST['status-type'] = '';
                            $statusType = '';
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
                        } else {
                            echo '<script type="text/javascript">alert("Failed to create patient ' . $patientName . '!");</script>';
                        }
                    } else if (trim($_POST['create']) == 'Transfer In') {
                        /*Update patient's info*/
                        date_default_timezone_set('Asia/Kuala_Lumpur');
                        $date = date("Y-m-d H:i:s");
                        // Create new patient code
                        // $newCodeResult = $db->getResultSet('patient_mstr', ['max(patient_code)'], ['patient_code not like "SPUB%"']);
                        // $newCodeRow = $newCodeResult->fetch_row();
                        // $newCodeInt = (int)(substr($newCodeRow[0], 2, 5) + 1);
                        // $format = 'SP%1$05d';
                        // $newCode = sprintf($format, $newCodeInt);

                        // Create a new patient code based on prefix of kk respectively
                        // Get prefix of kk based on user logged in
                        $prefixResult = $db->getResultSet(['user_mstr', 'gendoc_mstr'], ['gendoc_prefix'], ['user_name = "' . $_COOKIE['user'] . '"', 'user_active = "Y"', 'user_kk = gendoc_kk']);
                        $prefixRow = $prefixResult->fetch_row();
                        $prefix = $prefixRow[0];

                        // Get max number of patient
                        $maxPatCodeResult = $db->getResultSet('patient_mstr', ['max(patient_code)'], ['patient_spubin = "N"', 'patient_m1min = "N"', 'patient_code like "' . $prefix . '%"']);
                        $maxPatCodeRow = $maxPatCodeResult->fetch_row();
                        if (empty($maxPatCodeRow[0])) {
                            $maxPatCode = '000000';
                        } else {
                            $maxPatCode = $maxPatCodeRow[0];    
                        }

                        // Generate patient with own kk prefix
                        $newCodeInt = (int)(substr($maxPatCode, strlen($maxPatCode) - 5) + 1);
                        $format = $prefix . '%1$05d';
                        $newCode = sprintf($format, $newCodeInt);

                        $insertStatus = $db->insertData('patient_mstr',['patient_code', 'patient_name', 
                            'patient_methatype', 'patient_dose', 'patient_volume', 'patient_age', 
                            'patient_gender', 'patient_status', 'patient_ename', 'patient_econtact', 
                            'patient_active', 'patient_mobile', 'patient_tel', 'patient_email', 
                            'patient_addr1', 'patient_addr2', 'patient_addr3', 'patient_addr4', 
                            'patient_postcode', 'patient_city', 'patient_state', 'patient_kk', 
                            'user_created', 'date_created'],
                            [$newCode, trim($patientName), $patientMethatype, $patientDose, 
                            $patientVolume, $patientAge, $patientGender, $patientStatus, 
                            $patientEname, $patientEcontact, $patientActive, $patientMobile, 
                            $patientTel, $patientEmail, $patientAddr1, $patientAddr2, 
                            $patientAddr3, $patientAddr4, $patientPostcode, $patientCity, 
                            $patientState, $kk, $user, $date]);
                        $registerStatus = $db->insertData('transin_mstr',['transin_patcode', 
                            'transin_patname', 'transin_kk', 'user_created', 'date_created'],
                            [$newCode, $patientName, $kk, $user, $date]);
                        if ($insertStatus && $registerStatus) {
                            echo '<script type="text/javascript">alert("New transfer in patient ' . $patientName . ' with patient code, ' . $newCode . ' successfully created!");</script>';
                            $_POST['status-type'] = '';
                            $statusType = '';
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
                        } else {
                            echo '<script type="text/javascript">alert("Failed to create patient ' . $patientName . '!");</script>';
                        }
                    }
                }
            }
        }
        /*Get selected user's data from database*/
        else if (!empty($_POST['status-type']) && empty($_POST['create'])) {
            $statusType = $_POST['status-type'];
        }
    }
?>

<!DOCTYPE html>
<html>
<head>
    <title>MethaSys-Patient Maintenance</title>
    <link rel="stylesheet" type="text/css" href="../css/createMainStyle.css">
    <script type="text/javascript">
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
<div id="header">
    <?php require_once '../includes/titleHeader.php';?>
        <label>MethaSys - Create Patient Page</label>
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
                <form name="updateForm" method="post" autocomplete="off">
                    <label style='font-weight:bold;padding-left:5px;padding-right:15px;'>Register/Transfer In :</label>
                    <select id="status-type" name="status-type" style="width:365px;" oninput="this.form.submit()">
                        <?php
                            while ($row = $result->fetch_assoc()) {
                                echo '<option value="' . $row['status_name'] . '">' . $row['status_name'] . "</option>";
                            }
                        ?>
                    </select>
                    <script>document.getElementById("status-type").value = "<?php echo trim($statusType);?>";</script> 
                    <div id="patient-table-wrapper">
                        <table id="patient-update-table" style="visibility:
                            <?php 
                                if (($_SERVER['REQUEST_METHOD'] == 'POST') && (trim($_POST['status-type']) != '')) {
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
                                    <input type="text" id="patient-status" name="patient-status" value="NORMAL" readonly="readonly">
                                </td>
                                <th>City</th>
                                <td>
                                    <input type="text" id="patient-city" name="patient-city" class="bigCap">
                                </td>
                                <script>document.getElementById("patient-city").value = "<?php echo $patientCity;?>"</script>
                                
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
                        <input type="submit" id="create" name="create" value="" style="visibility:
                            <?php if (($_SERVER['REQUEST_METHOD'] == 'POST') && (trim($_POST['status-type']) != '')) {
                                    echo "visible";
                                } else { 
                                    echo "hidden";
                                }
                            ?>;">
                            <script>
                                var code = document.getElementById("status-type");
                                code.value = "<?php echo $statusType;?>";
                                if (code.value.trim() === "REGISTER") {
                                    document.getElementById("create").value = "Register";
                                } else {
                                    document.getElementById("create").value = "Transfer In";
                                }
                            </script>  
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
