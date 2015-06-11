<?php

    require_once 'classes/MySQLConnector.php';
    require_once 'classes/Date.php';

    $code = '';
    $name = '';
    $methatype = '';
    $dose = '';
    $volume = '';
    $status = '';
    $ename = '';
    $econtact = '';
    $attended = false;
    $oldPatient = false;
    $continuouslyAttd = false;
    $suspended = false;
    $lost = false;
    $terminated = false;
    $death = false;
    $transferOut = false;
    $spubOut = false;
    $todayReactivate = false;
    $patname = '';

    $db = new MySQLConnector('localhost', 'leoboey_db', 'methasys2015', 'leoboey_db');

    /*Get time zone*/
    $timeZone = new DateTimeZone('Asia/Kuala_Lumpur');

    /*Get today's date*/
    $currentDate = new Date($timeZone);
    $todayDate = $currentDate->getMySQLFormat($timeZone);

    $nextDate = new Date($timeZone);
    $nextDate->addDays(1);
    $tomorrowDate = $nextDate->getMySQLFormat($timeZone);

    /*Current time*/
    date_default_timezone_set('Asia/Kuala_Lumpur');
    $time = date('Y-m-d H:i:s');

    /*Get previous 1 day's date*/
    $currentDate2 = new Date($timeZone);
    $currentDate2->subDays(1);
    $prevOneDay = $currentDate2->getMySQLFormat();

    /*Get previous 4 day's date*/
    $currentDate3 = new Date($timeZone);
    $currentDate3->subDays(3);
    $prevFourDay = $currentDate3->getMySQLFormat();

    /*Get previous 2 weeks' date*/
    $currentDate4 = new Date($timeZone);
    $currentDate4->subDays(14);
    $prevTwoWeek = $currentDate4->getMySQLFormat();

    /*Get previous 1 month's date*/
    $currentDate5 = new Date($timeZone);
    $currentDate5->subDays(30);
    $prevOneMonth = $currentDate5->getMySQLFormat();

    /*Get patient code from javascript and santify it before used in database*/
    /*$code = trim($_REQUEST['code']);
    $code = strtoupper($db->realEscapeString($code));*/

    /*Get all patient code in database*/
    $patientCodeResult = $db->getResultSet('patient_mstr', ['patient_code'], ['patient_active = "Y"'], ['patient_id'], ['patient_code']);

    while ($row = $patientCodeResult->fetch_assoc()) {
        $code = trim($row['patient_code']);

        /*Check whether patient already attended for methadone*/
        $attended = $db->isExist('methascan_hist', ["methascan_patcode = '$code'", "methascan_date >= '$todayDate'", "methascan_date < '$tomorrowDate'"]);

        /*Get patient's name*/
        $patNameResult = $db->getResultSet('patient_mstr', ['patient_name'], 
            ['patient_code = "' . $code . '"', 'patient_active = "Y"']);
        $patNameRow = $patNameResult->fetch_assoc();
        $patname = $patNameRow['patient_name'];

        /*If patient not attend*/
        if (!$attended) {
            /*Determine whether patient is old patient*/
            $oldPatient = $db->isExist('methascan_hist',["methascan_patcode = '$code'"]);

            /*3 days suspended checking only for old patient.*/
            if ($oldPatient) {
                /*Check whether patient is reactivate by today*/
                $todayReactivate = $db->isExist('patient_mstr',["patient_code = '$code'", "patient_lastreactivate >= '$todayDate'", "patient_lastreactivate < '$tomorrowDate'"]);

                /*System skip checking if patient reactivated by today, else check whether patient failed to attend in 3 days continuously */
                if (!$todayReactivate) {
                    $continuouslyAttd = $db->isExist('methascan_hist', ["methascan_patcode = '$code'", "methascan_date >= '$prevFourDay'", "methascan_date < '$todayDate'"]);
                    $normalStatus = $db->isExist('patient_mstr', ["patient_code = '$code'", "patient_status = 'NORMAL'"]);

                    /*Update patient's status to SUSPENDED if patient failed to attend 3 days continuously*/
                    if (!$continuouslyAttd && $normalStatus) {
                        $updateStatus = $db->updateData('patient_mstr',['patient_status = "SUSPENDED"'], ["patient_code = '$code'"]);
                        $suspendedStatus = $db->insertData('suspended_mstr',['suspended_patcode', 
                                'suspended_patname', 'user_created', 'date_created'],
                                [$code, $patname, 'admin', $time]);
                    }
                }
            }
        }

        /*Check patient's suspended status*/
        $suspended = $db->isExist('patient_mstr', ["patient_code = '$code'", "patient_status = 'SUSPENDED'"]);

        /*If suspended check whether patient failed to attend in 2 weeks continuously*/
        if ($suspended) {
            /*Check whether patient is reactivate by today*/
            $todayReactivate = $db->isExist('patient_mstr',["patient_code = '$code'", "patient_lastreactivate >= '$todayDate'", "patient_lastreactivate < '$tomorrowDate'"]);

            /*System skip checking if patient reactivated by today, else check whether patient failed to attend in 2 weeks continuously */
            if (!$todayReactivate) {
                $continuouslyAttd = $db->isExist('methascan_hist', ["methascan_patcode = '$code'", "methascan_date >= '$prevTwoWeek'", "methascan_date < '$todayDate'"]);
                $suspendedStatus = $db->isExist('patient_mstr', ["patient_code = '$code'", "patient_status = 'SUSPENDED'"]);

                /*Update patient's status to LOST if patient failed to attend 2 weeks continuously*/
                if (!$continuouslyAttd && $suspendedStatus) {
                    $updateStatus = $db->updateData('patient_mstr',['patient_status = "LOST"'], ["patient_code = '$code'"]);
                    
                    /*Insert into lost_mstr*/
                    $lostStatus = $db->insertData('lost_mstr',['lost_patcode', 
                                'lost_patname', 'user_created', 'date_created'],
                                [$code, $patname, 'admin', $time]);

                    /*Get suspended id*/
                    $result = $db->getResultSet('suspended_mstr', ['suspended_id'], 
                        ['suspended_patcode = "' . $code . '"', 'suspended_active = "Y"'],
                        ['suspended_id'], ['suspended_id desc']);
                    $row = $result->fetch_assoc();
                    $suspendId = $row['suspended_id'];

                    /*Inactivate from suspended_mstr table*/
                    $updateStatus = $db->updateData('suspended_mstr',['suspended_active = "N"'], ["suspended_id = '$suspendId'"]);

                }
            }
        }

        /*Check patient's lost status*/
        $lost = $db->isExist('patient_mstr', ["patient_code = '$code'", "patient_status = 'LOST'"]);

        /*If lost check whether patient failed to attend in 1 month continuously*/
        if ($lost) {
            /*Check whether patient is reactivate by today*/
            $todayReactivate = $db->isExist('patient_mstr',["patient_code = '$code'", "patient_lastreactivate >= '$todayDate'", "patient_lastreactivate < '$tomorrowDate'"]);

            /*System skip checking if patient reactivated by today, else check whether patient failed to attend in 1 month continuously */
            if (!$todayReactivate) {
                $continuouslyAttd = $db->isExist('methascan_hist', ["methascan_patcode = '$code'", "methascan_date >= '$prevOneMonth'", "methascan_date < '$todayDate'"]);
                $lostStatus = $db->isExist('patient_mstr', ["patient_code = '$code'", "patient_status = 'LOST'"]);

                /*Update patient's status to TERMINATED if patient failed to attend 1 month continuously*/
                if (!$continuouslyAttd && $lostStatus) {
                    $updateStatus = $db->updateData('patient_mstr',['patient_status = "TERMINATED"'], ["patient_code = '$code'"]);

                    /*Insert into terminated_mstr*/
                    $terminatedStatus = $db->insertData('terminated_mstr',['terminated_patcode', 
                                'terminated_patname', 'user_created', 'date_created'],
                                [$code, $patname, 'admin', $time]);

                    /*Get lost id*/
                    $result = $db->getResultSet('lost_mstr', ['lost_id'], 
                        ['lost_patcode = "' . $code . '"', 'lost_active = "Y"'],
                        ['lost_id'], ['lost_id desc']);
                    $row = $result->fetch_assoc();
                    $lostId = $row['lost_id'];

                    /*Inactivate from suspended_mstr table*/
                    $updateStatus = $db->updateData('lost_mstr',['lost_active = "N"'], ["lost_id = '$lostId'"]);

                }
            }
        }

        /*Check patient's spub out status*/
        $spubOut = $db->isExist('patient_mstr', ["patient_code = '$code'", "patient_status = 'SPUB OUT'"]);

        /*If spub out, check whether patient already out of period*/
        if ($spubOut) {

            /*Check in spubout_mstr whether patient already out of period*/
            $withinPeriod = $db->isExist('spubout_mstr', ['spubout_patcode = "' . $code . '"', 'spubout_supplyfrom <= "' . $todayDate . '"', 'spubout_supplyto >= "' . $todayDate . '"']);
            $spubOutStatus = $db->isExist('patient_mstr', ["patient_code = '$code'", "patient_status = 'SPUB OUT'"]);

            /*Update patient's status to NORMAL if patient out of period*/
            if (!$withinPeriod && $spubOutStatus) {
                $updateStatus = $db->updateData('patient_mstr',['patient_status = "NORMAL"', 'patient_lastreactivate = "' . $time . '"'], 
                    ["patient_code = '$code'"]);
            }
        }
    }
    
?>
