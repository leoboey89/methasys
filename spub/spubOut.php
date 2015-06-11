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

    /*Initialize variables*/
    $spubCode = '';
    $patientCode = '';
    $medState = '';
    $medCenter = '';
    $medAddr = '';
    $medTel = '';
    $medFax = '';
    $patientName = '';
    $patientId = '';
    $patientAge = '';
    $patientGender = '';
    $patientContact = '';
    $patientPresId = '';
    $patientPresDate = $todayDate;
    $patientDose = '';
    $patientSupplyFrom = $todayDate;
    $patientSupplyTo = $todayDate;
    $patientSupplyDays = '';
    $patientPic = '';
    $missing = array();
    $user = $_COOKIE['user'];
    $kk = $_COOKIE['kk'];

    /*Get all SPUB Out patient for update purpose*/
    $spubPatientResult = $db->getResultSet(['spubout_mstr', 'patient_mstr'], 
        ['spubout_patname', 'spubout_patcode'], 
        ['spubout_supplyfrom <= "' . $todayDate . '"', 'spubout_supplyto >= "' . $todayDate . '"', 
        'spubout_active = "Y"', 'spubout_patcode = patient_code', 
        'patient_active = "Y"', 'patient_kk = "' . $kk . '"',
        'spubout_kk = "' . $kk . '"', 'patient_spubout = "Y"'], 
        ['spubout_patcode'], ['spubout_patcode']);
    /*For create new SPUB Out for patient, only allow patient with normal status to be selected*/
    $patientResult = $db->getResultSet('patient_mstr', ['patient_code', 'patient_name'], 
        ['patient_status = "NORMAL"', 'patient_active = "Y"', 
        'patient_spubout = "N"', 'patient_spubin = "N"',
        'patient_m1mout = "N"', 'patient_m1min = "N"',
        'patient_kk = "' . $kk .'"']);

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        /*If Save button is pressed*/
        if (!empty($_POST['insert']) && !empty($_POST['spub-code'])) {
            /*Get patient's info from field*/
            $spubCode = $_POST['spub-code'];
            $patientCode = $_POST['patient-code'];
            $medState = strtoupper($_POST['patient-medstate']);
            $medCenter = strtoupper($_POST['patient-medcenter']);
            $medAddr = strtoupper($_POST['patient-medaddr']);
            $medTel = strtoupper($_POST['patient-medtel']);
            $medFax = strtoupper($_POST['patient-medfax']);
            $patientName = strtoupper($_POST['patient-patname']);
            $patientId = strtoupper($_POST['patient-patid']);
            $patientAge = strtoupper($_POST['patient-age']);
            $patientGender = strtoupper($_POST['patient-gender']);
            $patientContact = strtoupper($_POST['patient-pattel']);
            $patientPresId = strtoupper($_POST['patient-presid']);
            $patientPresDate = strtoupper($_POST['patient-presdate']);
            $patientDose = strtoupper($_POST['patient-dose']);
            $patientSupplyFrom = strtoupper($_POST['patient-supplyfrom']);
            $patientSupplyTo = strtoupper($_POST['patient-supplyto']);
            $patientSupplyDays = strtoupper($_POST['patient-supplydays']);
            $patientPic = strtoupper($_POST['patient-pic']);

            /*Create an Validator object to validate input field*/
            $validator = new Validator(['patient-medstate', 'patient-medcenter', 'patient-medaddr', 'patient-medtel', 
                'patient-medfax', 'patient-patname', 'patient-patid', 'patient-age', 
                'patient-gender', 'patient-pattel', 'patient-presid', 'patient-presdate', 
                'patient-dose', 'patient-supplyfrom', 'patient-supplyto', 'patient-supplydays', 
                'patient-pic', 'patient-code']);

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
                $insertStatus = $db->insertData('spubout_mstr',
                        ['spubout_state', 'spubout_addr', 'spubout_fromkk', 'spubout_tel', 
                        'spubout_fax', 'spubout_patname', 'spubout_patcode', 'spubout_patid', 
                        'spubout_age', 'spubout_gender', 'spubout_pattel', 'spubout_presid', 
                        'spubout_presdate', 'spubout_presdose', 'spubout_supplyfrom', 'spubout_supplyto', 
                        'spubout_supplydays', 'spubout_pic', 'spubout_kk', 'user_created', 
                        'date_created'],
                        [$medState, $medAddr, $medCenter, $medTel, 
                        $medFax, $patientName, $patientCode, $patientId, 
                        $patientAge, $patientGender, $patientContact, $patientPresId, 
                        $patientPresDate, $patientDose, $patientSupplyFrom, $patientSupplyTo, 
                        $patientSupplyDays, $patientPic, $kk, $user, 
                        $date]);

                /*Update patient status to SPUB OUT*/
                $updateStatus = $db->updateData('patient_mstr',
                    ['patient_status = "SPUB OUT"', 'patient_spubout = "Y"', 'user_updated = "' . $user . '"', 'date_updated = "' . $date . '"'], 
                    ['patient_code = "' . $patientCode . '"', 'patient_kk = "' . $kk . '"']);

                /*Display status of creating SPUB for patient*/
                if ($insertStatus && $updateStatus) {
                    echo '<script type="text/javascript">alert("' . $patientName . ' is now SPUB Out")</script>';
                } else {
                    echo '<script type="text/javascript">alert("Failed to create SPUB Out for ' . $patientName . '")</script>';
                }

                /*Get all SPUB Out patient for update purpose*/
                $spubPatientResult = $db->getResultSet(['spubout_mstr', 'patient_mstr'], 
                    ['spubout_patname', 'spubout_patcode'], 
                    ['spubout_supplyfrom <= "' . $todayDate . '"', 'spubout_supplyto >= "' . $todayDate . '"', 
                    'spubout_active = "Y"', 'spubout_patcode = patient_code', 
                    'patient_active = "Y"', 'spubout_kk = "' . $kk . '"',
                    'patient_kk = "' . $kk . '"', 'patient_spubout = "Y"'], 
                    ['spubout_patcode'], ['spubout_patcode']);

                /*For create new SPUB Out for patient, only allow patient with normal status to be selected*/
                $patientResult = $db->getResultSet('patient_mstr', ['patient_code', 'patient_name'], 
                    ['patient_status = "NORMAL"', 'patient_active = "Y"', 
                    'patient_spubout = "N"', 'patient_spubin = "N"',
                    'patient_m1mout = "N"', 'patient_m1min = "N"',
                    'patient_kk = "' . $kk .'"']);


                /*Restructure page*/
                $spubCode = '';
            } 
        }
        /*If Return button pressed, change back patient's status to normal*/
        else if (!empty($_POST['return']) && !empty($_POST['spub-code'])){
            /*Get current date and time*/
            date_default_timezone_set('Asia/Kuala_Lumpur');
            $time = date('Y-m-d H:i:s');
            $date = date('Y-m-d');

            $patientName = strtoupper($_POST['patient-patname']);

            /*Update patient's status to NORMAL*/
            $spubCode = $_POST['spub-code'];
            $updatePatStatus = $db->updateData('patient_mstr',['patient_status = "NORMAL"', 'patient_spubout = "N"', 'patient_lastreactivate = "' . $time . '"', 
                'user_updated = "' . $user . '"', 'date_updated = "' . $date . '"'], 
                    ["patient_code = '$spubCode'", "patient_kk = '$kk'"]);
            $latestSPUBId = $db->getResultSet('spubout_mstr', 
                ['max(spubout_id) as spubout_id'], 
                ['spubout_patcode = "' . $spubCode . '"', 'spubout_active = "Y"', 
                'spubout_supplyfrom <= "' . $todayDate . '"', 'spubout_supplyto >= "' . $todayDate . '"',
                'spubout_kk = "' . $kk . '"']);
            if ($row = $latestSPUBId->fetch_assoc()) {
                $updateSPUBStatus = $db->updateData('spubout_mstr', ['spubout_supplyto = "' . $todayDate . '"'], 
                    ['spubout_id = "' . $row['spubout_id'] . '"', 'spubout_supplyfrom <= "' . $todayDate . '"', 
                    'spubout_supplyto >= "' . $todayDate . '"', 'spubout_kk = "' . $kk . '"']);
            }
            

            if ($updatePatStatus) {
                echo '<script type="text/javascript">alert("' . $patientName . ' returned, patient allows to take methadone.")</script>';
            } 

            /*Get all SPUB Out patient for update purpose*/
            $spubPatientResult = $db->getResultSet(['spubout_mstr', 'patient_mstr'], 
                ['spubout_patname', 'spubout_patcode'], 
                ['spubout_supplyfrom <= "' . $todayDate . '"', 'spubout_supplyto >= "' . $todayDate . '"', 
                'spubout_active = "Y"', 'spubout_patcode = patient_code', 
                'patient_active = "Y"', 'spubout_kk = "' . $kk . '"',
                'patient_kk = "' . $kk . '"', 'patient_spubout = "Y"'], 
                ['spubout_patcode'], ['spubout_patcode']);

            /*For create new SPUB Out for patient, only allow patient with normal status to be selected*/
            $patientResult = $db->getResultSet('patient_mstr', ['patient_code', 'patient_name'], 
                ['patient_status = "NORMAL"', 'patient_active = "Y"', 
                'patient_spubout = "N"', 'patient_spubin = "N"',
                'patient_m1mout = "N"', 'patient_m1min = "N"',
                'patient_kk = "' . $kk .'"']);


            /*Restructure page*/
            $spubCode = '';
        }
        /*If Delete button pressed, change back patient's status to normal*/
        else if (!empty($_POST['delete']) && !empty($_POST['spub-code'])){
            /*Get current date and time*/
            date_default_timezone_set('Asia/Kuala_Lumpur');
            $time = date('Y-m-d H:i:s');

            $patientName = strtoupper($_POST['patient-patname']);

            /*Update patient's status to NORMAL*/
            $spubCode = $_POST['spub-code'];
            $updateStatus = $db->updateData('patient_mstr',['patient_status = "NORMAL"', 'patient_spubout = "N"', 'patient_lastreactivate = "' . $time . '"', 
                'user_updated = "' . $user . '"', 'date_updated = "' . $date . '"'], 
                    ["patient_code = '$spubCode'", "patient_kk = '$kk'"]);
            $latestSPUBId = $db->getResultSet('spubout_mstr', 
                ['max(spubout_id) as spubout_id'], 
                ['spubout_patcode = "' . $spubCode . '"', 'spubout_active = "Y"', 
                'spubout_supplyfrom <= "' . $todayDate . '"', 'spubout_supplyto >= "' . $todayDate . '"',
                'spubout_kk = "' . $kk . '"']);
            if ($row = $latestSPUBId->fetch_assoc()) {
                $updateSPUBStatus = $db->updateData('spubout_mstr', ['spubout_active = "N"', 'user_updated = "' . $user . '"', 'date_updated = "' . $date . '"'], 
                ['spubout_id = "' . $row['spubout_id'] . '"', 'spubout_supplyfrom <= "' . $todayDate . '"', 
                'spubout_supplyto >= "' . $todayDate . '"', 'spubout_kk = "' . $kk . '"']);                
            }

            if ($updateStatus && $updateSPUBStatus) {
                echo '<script type="text/javascript">alert("' . $patientName . ' in SPUB OUT deleted.")</script>';
            } 

            /*Get all SPUB Out patient for update purpose*/
            $spubPatientResult = $db->getResultSet(['spubout_mstr', 'patient_mstr'], 
                ['spubout_patname', 'spubout_patcode'], 
                ['spubout_supplyfrom <= "' . $todayDate . '"', 'spubout_supplyto >= "' . $todayDate . '"', 
                'spubout_active = "Y"', 'spubout_patcode = patient_code', 
                'patient_active = "Y"', 'patient_kk = "' . $kk . '"',
                'spubout_kk = "' . $kk . '"', 'patient_spubout = "Y"'], 
                ['spubout_patcode'], ['spubout_patcode']);
            /*For create new SPUB Out for patient, only allow patient with normal status to be selected*/
            $patientResult = $db->getResultSet('patient_mstr', ['patient_code', 'patient_name'], 
                ['patient_status = "NORMAL"', 'patient_active = "Y"', 
                'patient_spubout = "N"', 'patient_spubin = "N"',
                'patient_m1mout = "N"', 'patient_m1min = "N"',
                'patient_kk = "' . $kk .'"']);


            /*Restructure page*/
            $spubCode = '';
        }
        /*When Update button is pressed*/
        else if (!empty($_POST['update']) && !empty($_POST['spub-code'])) {
            /*Get patient's info from field*/
            $spubCode = $_POST['spub-code'];
            $patientCode = $_POST['spub-code'];
            $medState = strtoupper($_POST['patient-medstate']);
            $medCenter = strtoupper($_POST['patient-medcenter']);
            $medAddr = strtoupper($_POST['patient-medaddr']);
            $medTel = strtoupper($_POST['patient-medtel']);
            $medFax = strtoupper($_POST['patient-medfax']);
            $patientName = strtoupper($_POST['patient-patname']);
            $patientId = strtoupper($_POST['patient-patid']);
            $patientAge = strtoupper($_POST['patient-age']);
            $patientGender = strtoupper($_POST['patient-gender']);
            $patientContact = strtoupper($_POST['patient-pattel']);
            $patientPresId = strtoupper($_POST['patient-presid']);
            $patientPresDate = strtoupper($_POST['patient-presdate']);
            $patientDose = strtoupper($_POST['patient-dose']);
            $patientSupplyFrom = strtoupper($_POST['patient-supplyfrom']);
            $patientSupplyTo = strtoupper($_POST['patient-supplyto']);
            $patientSupplyDays = strtoupper($_POST['patient-supplydays']);
            $patientPic = strtoupper($_POST['patient-pic']);

            /*Create an Validator object to validate input field*/
            $validator = new Validator(['patient-medstate', 'patient-medcenter', 'patient-medaddr', 'patient-medtel', 
                'patient-medfax', 'patient-patname', 'patient-patid', 'patient-age', 
                'patient-gender', 'patient-pattel', 'patient-presid', 'patient-presdate', 
                'patient-dose', 'patient-supplyfrom', 'patient-supplyto', 'patient-supplydays', 
                'patient-pic']);

            $missing = $validator->getMissingInput();

            /*If any compulsary field is not filled up*/
            if (!empty($missing)) {

                /*Get missing field from Validator object*/
                $missing = $validator->getMissingInput();
            } else {
                /*Get current date and time*/
                date_default_timezone_set('Asia/Kuala_Lumpur');
                $date = date("Y-m-d H:i:s");

                $latestPatientCodeId = $db->getResultSet('spubout_mstr', 
                    ['max(spubout_id) as spubout_id'], 
                    ['spubout_patcode = "' . $spubCode . '"', 'spubout_active = "Y"', 
                    'spubout_supplyfrom <= "' . $todayDate . '"', 'spubout_supplyto >= "' . $todayDate . '"',
                    'spubout_kk = "' . $kk . '"']);

                if ($row = $latestPatientCodeId->fetch_assoc()) {        
                    /*Update SPUB OUT info of patient*/
                    $updateStatus = $db->updateData('spubout_mstr',
                            ["spubout_state='$medState'", "spubout_addr='$medAddr'", 
                            "spubout_fromkk='$medCenter'", "spubout_tel='$medTel'", 
                            "spubout_fax='$medFax'", "spubout_patname='$patientName'", 
                            "spubout_patid='$patientId'", "spubout_age='$patientAge'", 
                            "spubout_gender='$patientGender'", "spubout_pattel='$patientContact'", 
                            "spubout_presid='$patientPresId'", "spubout_presdate='$patientPresDate'", 
                            "spubout_presdose='$patientDose'", "spubout_supplyfrom='$patientSupplyFrom'", 
                            "spubout_supplyto='$patientSupplyTo'", "spubout_supplydays='$patientSupplyDays'", 
                            "spubout_pic='$patientPic'", "user_updated ='" . $user . "'", 
                            "date_updated='$date'"],
                            ['spubout_id = "' . $row['spubout_id'] . '"', 'spubout_kk = "' . $kk . '"']);
                };

                /*Display update status*/
                if ($updateStatus) {
                    echo '<script type="text/javascript">alert("' . $patientName . ' updated!")</script>';
                } 

                /*Get all SPUB Out patient for update purpose*/
                $spubPatientResult = $db->getResultSet(['spubout_mstr', 'patient_mstr'], 
                    ['spubout_patname', 'spubout_patcode'], 
                    ['spubout_supplyfrom <= "' . $todayDate . '"', 'spubout_supplyto >= "' . $todayDate . '"', 
                    'spubout_active = "Y"', 'spubout_patcode = patient_code', 
                    'patient_active = "Y"', 'patient_kk = "' . $kk . '"',
                    'spubout_kk = "' . $kk . '"', 'patient_spubout = "Y"'], 
                    ['spubout_patcode'], ['spubout_patcode']);

                /*Restructure page*/
                $spubCode = '';
            } 
        } else if (!empty($_POST['spub-code'])) {
            if (($_POST['spub-code'] == 'Create New') && !empty($_POST['patient-code'])) {
                $spubCode = $_POST['spub-code'];
                $patientCode = $_POST['patient-code'];
                $medState = '';
                $medCenter = '';
                $medAddr = '';
                $medTel = '';
                $medFax = '';
                $patientName = '';
                $patientId = '';
                $patientAge = '';
                $patientGender = '';
                $patientContact = '';
                $patientPresId = '';
                $patientPresDate = $todayDate;
                $patientDose = '';
                $patientSupplyFrom = $todayDate;
                $patientSupplyTo = $todayDate;
                $patientSupplyDays = '';
                $patientPic = '';

                /*Get selected patient's default info*/
                $patientResult = $db->getResultSet('patient_mstr', 
                    ['patient_name', 'patient_age', 'patient_gender', 'patient_tel', 
                    'patient_dose'], 
                    ['patient_code = "' . $patientCode . '"', 'patient_kk = "' . $kk . '"']);
                if ($row = $patientResult->fetch_assoc()){
                    $patientName = $row['patient_name'];
                    $patientAge = $row['patient_age'];
                    $patientGender = $row['patient_gender'];
                    $patientContact = $row['patient_tel'];
                    $patientDose = $row['patient_dose'];
                }

                /*Get all SPUB Out patient for update purpose*/
                $spubPatientResult = $db->getResultSet(['spubout_mstr', 'patient_mstr'], 
                    ['spubout_patname', 'spubout_patcode'], 
                    ['spubout_supplyfrom <= "' . $todayDate . '"', 'spubout_supplyto >= "' . $todayDate . '"', 
                    'spubout_active = "Y"', 'spubout_patcode = patient_code', 
                    'patient_active = "Y"', 'spubout_kk = "' . $kk . '"',
                    'patient_kk = "' . $kk . '"', 'patient_spubout = "Y"'], 
                    ['spubout_patcode'], ['spubout_patcode']);
                /*For create new SPUB Out for patient, only allow patient with normal status to be selected*/
                $patientResult = $db->getResultSet('patient_mstr', ['patient_code', 'patient_name'], 
                    ['patient_status = "NORMAL"', 'patient_active = "Y"', 
                    'patient_spubout = "N"', 'patient_spubin = "N"',
                    'patient_m1mout = "N"', 'patient_m1min = "N"',
                    'patient_kk = "' . $kk .'"']);

            } else {
                /*Get selected SPUB Out patient's code*/
                $spubCode = $_POST['spub-code'];

                $latestPatientCodeId = $db->getResultSet('spubout_mstr', 
                    ['max(spubout_id) as spubout_id'], 
                    ['spubout_patcode = "' . $spubCode . '"', 'spubout_active = "Y"', 
                    'spubout_supplyfrom <= "' . $todayDate . '"', 'spubout_supplyto >= "' . $todayDate . '"',
                    'spubout_kk = "' . $kk . '"']);
                if ($row = $latestPatientCodeId->fetch_assoc()){
                    /*Get selected SPUB Out patient's info*/
                    $spubPatientResult = $db->getResultSet('spubout_mstr',
                        ['spubout_state', 'spubout_addr', 'spubout_fromkk', 'spubout_tel', 
                        'spubout_fax', 'spubout_patname', 'spubout_patcode', 'spubout_patid', 
                        'spubout_age', 'spubout_gender', 'spubout_pattel', 'spubout_presid', 
                        'spubout_presdate', 'spubout_presdose', 'spubout_supplyfrom', 'spubout_supplyto', 
                        'spubout_supplydays', 'spubout_pic', 'user_created', 'date_created'], 
                        ['spubout_id = "' . $row['spubout_id'] . '"', 'spubout_kk = "' . $kk . '"']);
                }

                if ($row = $spubPatientResult->fetch_assoc()) {
                    $medState = $row['spubout_state'];
                    $medCenter = $row['spubout_fromkk'];
                    $medAddr = $row['spubout_addr'];
                    $medTel = $row['spubout_tel'];
                    $medFax = $row['spubout_fax'];
                    $patientName = $row['spubout_patname'];
                    $patientId = $row['spubout_patid'];
                    $patientAge = $row['spubout_age'];
                    $patientGender = $row['spubout_gender'];
                    $patientContact = $row['spubout_pattel'];
                    $patientPresId = $row['spubout_presid'];
                    $patientPresDate = substr($row['spubout_presdate'], 0, 10);;
                    $patientDose = $row['spubout_presdose'];
                    $patientSupplyFrom = substr($row['spubout_supplyfrom'], 0, 10);
                    $patientSupplyTo = substr($row['spubout_supplyto'], 0, 10);
                    $patientSupplyDays = $row['spubout_supplydays'];
                    $patientPic = $row['spubout_pic'];
                }        

                /*Get all SPUB Out patient for update purpose*/
                $spubPatientResult = $db->getResultSet(['spubout_mstr', 'patient_mstr'], 
                    ['spubout_patname', 'spubout_patcode'], 
                    ['spubout_supplyfrom <= "' . $todayDate . '"', 'spubout_supplyto >= "' . $todayDate . '"', 
                    'spubout_active = "Y"', 'spubout_patcode = patient_code', 
                    'patient_active = "Y"', 'patient_kk = "' . $kk . '"',
                    'spubout_kk = "' . $kk . '"', 'patient_spubout = "Y"'], 
                    ['spubout_patcode'], ['spubout_patcode']);
            }
        } 
    }

?>

<!DOCTYPE html>
<html>
<head>
    <title>MethaSys-SPUB Out</title>
    <link rel="stylesheet" type="text/css" href="../css/spubOutStyle.css">
    <script src="../scripts/jquery-1.11.2.min.js"></script>
    <script>
        $("document").ready(function(){
            $("#print").click(function(){

                var spubWin = window.open('../scripts/printSPUB.php', 'retenWindow','fullscreen=yes');
                var medState = document.getElementById("patient-medstate").value;
                var medCenter = document.getElementById("patient-medcenter").value;
                var medAddr = document.getElementById("patient-medaddr").value;
                var medTel = document.getElementById("patient-medtel").value;
                var medFax = document.getElementById("patient-medfax").value;
                var patientName = document.getElementById("patient-patname").value;
                var patientId = document.getElementById("patient-patid").value;
                var patientAge = document.getElementById("patient-age").value;
                var patientGender = document.getElementById("patient-gender").value;
                var patientContact = document.getElementById("patient-pattel").value;
                var patientPresId = document.getElementById("patient-presid").value;
                var patientPresDate = document.getElementById("patient-presdate").value;
                var patientDose = document.getElementById("patient-dose").value;
                var patientSupplyFrom = document.getElementById("patient-supplyfrom").value;
                var patientSupplyTo = document.getElementById("patient-supplyto").value;
                var patientSupplyDays = document.getElementById("patient-supplydays").value;
                var patientPic = document.getElementById("patient-pic").value;
                
                spubWin.onload = function(){
                    spubWin.document.getElementById("state2").innerHTML = medState;
                    spubWin.document.getElementById("centre2").innerHTML = medCenter;
                    spubWin.document.getElementById("address3").innerHTML = medAddr;
                    var address4 = spubWin.document.getElementById("address4");
                    address4.parentNode.removeChild(address4);
                    spubWin.document.getElementById("tel2").innerHTML = medTel;
                    spubWin.document.getElementById("fax2").innerHTML = medFax;
                    spubWin.document.getElementById("name-label").innerHTML = patientName;
                    spubWin.document.getElementById("id-label").innerHTML = patientId;
                    spubWin.document.getElementById("age-label").innerHTML = patientAge;
                    spubWin.document.getElementById("tel-label").innerHTML = patientContact;
                    spubWin.document.getElementById("prescription-no-label").innerHTML = patientPresId;
                    spubWin.document.getElementById("prescription-date-label").innerHTML = patientPresDate;
                    spubWin.document.getElementById("prescription-dos-label").innerHTML = patientDose;
                    spubWin.document.getElementById("date-cont2").innerHTML = patientSupplyFrom + ' -> ' + patientSupplyTo;
                    spubWin.document.getElementById("period2").innerHTML = patientSupplyDays;
                    spubWin.document.getElementById("pic-label").innerHTML = patientPic;
                }            
            });

            $("#patient-supplyfrom").change(function(){
                supplyFrom = $("patient-supplyfrom").val();
                supplyTo = $("patient-supplyto").val();
                date1 = new Date(supplyFrom);
                year = date1.getFullYear();
                $("patient-supplydays").val(year);
                    
            });
        });
    </script>
</head>
<body>
<div id="header">
    <?php require_once '../includes/titleHeader.php';?>
        <label>MethaSys - SPUB Out Page</label>
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
        <div id="patient-spub">
            <form method='post' autocomplete="off">
                <p id="spub-code-content" style="font-weight:bold;padding-left:20px;">
                    SPUB Out :
                    <select id="spub-code" name="spub-code" onchange="this.form.submit()">
                        <option></option>
                        <option>Create New</option>
                        <?php
                            while($row = $spubPatientResult->fetch_assoc()) {
                                echo '<option value="' . $row['spubout_patcode'] . '">' . $row['spubout_patcode'] . ' >>> ' . $row['spubout_patname'] . '</option>';
                            }
                        ?>
                    </select>
                    <script>document.getElementById("spub-code").value = "<?php echo trim($spubCode);?>";</script> 
                    <button id="print">Print</button>
                </p> 
                <div id="spubForm" style="visibility:
                    <?php 
                        if ($spubCode == '') {
                            echo "hidden";
                        } else { 
                            echo "visible";
                        }
                    ?>;">
                    <div id="patient-code-content"style="visibility:
                        <?php if ($spubCode == 'Create New') {
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
                    <table>
                        <tr>
                            <th>State</th>
                            <td><input id="patient-medstate" name="patient-medstate" type="text"></td>   
                            <th>Contact Number</th>
                            <td><input id="patient-pattel" name="patient-pattel" type="text"></td>                         
                        </tr>
                        <script>document.getElementById("patient-medstate").value = "<?php echo trim($medState);?>";</script> 
                        <script>document.getElementById("patient-pattel").value = "<?php echo trim($patientContact);?>";</script> 
                        <tr>
                            <td></td>
                            <td>
                                <?php
                                    if (($_SERVER['REQUEST_METHOD'] == 'POST') && in_array('patient-medstate', $missing)) {
                                        echo '<span style="color:red">Please fill in state</span>';             
                                    }
                                ?>
                            </td>
                            <td></td>
                            <td>
                                <?php
                                    if (($_SERVER['REQUEST_METHOD'] == 'POST') && in_array('patient-pattel', $missing)) {
                                        echo '<span style="color:red">Please fill in patient\'s contact number</span>';             
                                    }
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <th>Medical Center</th>
                            <td><input id="patient-medcenter" name="patient-medcenter" type="text"></td>  
                            <th>Prescription Number</th>
                            <td><input id="patient-presid" name="patient-presid" type="text"></td>                          
                        </tr>
                        <script>document.getElementById("patient-medcenter").value = "<?php echo trim($medCenter);?>";</script> 
                        <script>document.getElementById("patient-presid").value = "<?php echo trim($patientPresId);?>";</script>
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
                                    if (($_SERVER['REQUEST_METHOD'] == 'POST') && in_array('patient-presid', $missing)) {
                                        echo '<span style="color:red">Please fill in patient\'s prescription id</span>';             
                                    }
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <th>Address</th>
                            <td><input id="patient-medaddr" name="patient-medaddr" type="text"></td>    
                            <th>Prescription Date</th>
                            <td><input id="patient-presdate" name="patient-presdate" type="date" value="<?php echo $todayDate;?>"></td>                        
                        </tr>
                        <script>document.getElementById("patient-medaddr").value = "<?php echo trim($medAddr);?>";</script> 
                        <script>document.getElementById("patient-presdate").value = "<?php echo trim($patientPresDate);?>";</script>
                        <tr>
                            <td></td>
                            <td>
                                <?php
                                    if (($_SERVER['REQUEST_METHOD'] == 'POST') && in_array('patient-medaddr', $missing)) {
                                        echo '<span style="color:red">Please fill in medical center address</span>';             
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
                            <th>Telephone Number</th>
                            <td><input id="patient-medtel" name="patient-medtel" type="text"></td>        
                            <th>Methadone Dose</th>
                            <td><input type="number" min="5" max="200" step="5" id="patient-dose" name="patient-dose" onchange="autoCalVolume()"></td>                    
                        </tr>
                        <script>document.getElementById("patient-medtel").value = "<?php echo trim($medTel);?>";</script> 
                        <script>document.getElementById("patient-dose").value = "<?php echo trim($patientDose);?>";</script>
                        <tr>
                            <td></td>
                            <td>
                                <?php
                                    if (($_SERVER['REQUEST_METHOD'] == 'POST') && in_array('patient-medtel', $missing)) {
                                        echo '<span style="color:red">Please fill in medical telephone number</span>';             
                                    }
                                ?>
                            </td>
                            <td></td>
                            <td>
                                <?php
                                    if (($_SERVER['REQUEST_METHOD'] == 'POST') && in_array('patient-dose', $missing)) {
                                        echo '<span style="color:red">Please fill in patient\'s prescription dose</span>';             
                                    }
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <th>Fax Number</th>
                            <td><input id="patient-medfax" name="patient-medfax" type="text"></td>   
                            <th>Provide From</th>
                            <td><input type="date" id="patient-supplyfrom" name="patient-supplyfrom" value="<?php echo $todayDate;?>"></td>                         
                        </tr>
                        <script>document.getElementById("patient-medfax").value = "<?php echo trim($medFax);?>";</script> 
                        <script>document.getElementById("patient-supplyfrom").value = "<?php echo trim($patientSupplyFrom);?>";</script>
                        <tr>
                            <td></td>
                            <td>
                                <?php
                                    if (($_SERVER['REQUEST_METHOD'] == 'POST') && in_array('patient-medfax', $missing)) {
                                        echo '<span style="color:red">Please fill in medical fax number</span>';             
                                    }
                                ?>
                            </td>
                            <td></td>
                            <td>
                                <?php
                                    if (($_SERVER['REQUEST_METHOD'] == 'POST') && in_array('patient-supplyfrom', $missing)) {
                                        echo '<span style="color:red">Please fill in patient\'s provide from date</span>';             
                                    }
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <th>Patient Name</th>
                            <td><input id="patient-patname" name="patient-patname" type="text"></td>   
                            <th>Provide To</th>
                            <td><input type="date" id="patient-supplyto" name="patient-supplyto" value="<?php echo $todayDate;?>"></td>                      
                        </tr>
                        <script>document.getElementById("patient-patname").value = "<?php echo trim($patientName);?>";</script> 
                        <script>document.getElementById("patient-supplyto").value = "<?php echo trim($patientSupplyTo);?>";</script>
                        <tr>
                            <td></td>
                            <td>
                                <?php
                                    if (($_SERVER['REQUEST_METHOD'] == 'POST') && in_array('patient-patname', $missing)) {
                                        echo '<span style="color:red">Please fill in patient\'s name</span>';             
                                    }
                                ?>
                            </td>
                            <td></td>
                            <td>
                                <?php
                                    if (($_SERVER['REQUEST_METHOD'] == 'POST') && in_array('patient-supplyto', $missing)) {
                                        echo '<span style="color:red">Please fill in patient\'s provide to date</span>';             
                                    }
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <th>Patient ID</th>
                            <td><input id="patient-patid" name="patient-patid" type="text"></td>
                            <th>Provide period</th>
                            <td><input id="patient-supplydays" name="patient-supplydays" type="text"></td>
                        </tr>
                        <script>document.getElementById("patient-patid").value = "<?php echo trim($patientId);?>";</script>
                        <script>document.getElementById("patient-supplydays").value = "<?php 
                                $date1 = new DateTime($patientSupplyFrom);
                                $date2 = new DateTime($patientSupplyTo);
                                $interval = $date1->diff($date2);
                                echo intVal($interval->format('%a'))+1;
                            ?>";</script> 
                        <tr>
                            <td></td>
                            <td>
                                <?php
                                    if (($_SERVER['REQUEST_METHOD'] == 'POST') && in_array('patient-patid', $missing)) {
                                        echo '<span style="color:red">Please fill in patient\'s id</span>';             
                                    }
                                ?>
                            </td>
                            <th></th>
                            <th></th>
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
                            <th>Pharmacist Reference</th>
                            <td><input id="patient-pic" name="patient-pic" type="text"></td>
                        </tr>
                        <script>document.getElementById("patient-pic").value = "<?php echo trim($patientPic);?>";</script>
                        <tr>
                            <td></td>
                            <td>
                                <?php
                                    if (($_SERVER['REQUEST_METHOD'] == 'POST') && in_array('patient-age', $missing)) {
                                        echo '<span style="color:red">Please fill in patient\'s age</span>';             
                                    }
                                ?>
                            </td>
                            <td></td>
                            <td>
                                <?php
                                    if (($_SERVER['REQUEST_METHOD'] == 'POST') && in_array('patient-pic', $missing)) {
                                        echo '<span style="color:red">Please fill in reference pharmacist</span>';             
                                    }
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <th>Gender</th>
                            <td>
                                <select id="patient-gender" name="patient-gender" class="bigCap">
                                    <?php 
                                        if (trim($patientAge) == 'MALE') {
                                            echo '<option selected="selected" value="MALE">MALE</option>';
                                            echo '<option value="FEMALE">FEMALE</option>';
                                        } else if (trim($patientAge) == 'FEMALE'){
                                            echo '<option value="MALE">MALE</option>';
                                            echo '<option selected="selected" value="FEMALE">FEMALE</option>';
                                        } else {
                                            echo '<option value="MALE" selected="selected">MALE</option>';
                                            echo '<option value="FEMALE">FEMALE</option>';
                                        }
                                    ?>
                                </select>
                            </td>
                            <th></th>
                            <td></td>
                        </tr>
                        <tr>
                            <td></td>
                            <td>
                                <?php
                                    if (($_SERVER['REQUEST_METHOD'] == 'POST') && in_array('patient-gender', $missing)) {
                                        echo '<span style="color:red">Please fill in patient\'s gender</span>';             
                                    }
                                ?>
                            </td>
                            <td></td>
                            <td></td>
                        </tr>
                    </table>
                    <?php
                        if ($spubCode == 'Create New') {
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
