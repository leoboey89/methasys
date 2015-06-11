<?php
    set_include_path('/home/leoboey/public_html/methasys/classes/');
    require_once 'MySQLConnector.php';
    require_once 'Date.php';

    // require_once '../classes/MySQLConnector.php';
    // require_once '../classes/Date.php';

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
    $todayDay = $currentDate->getDayName();
    $todayDate = $currentDate->getMySQLFormat($timeZone);
    $days = $currentDate->getDayNumber();

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

    $kkHQResult = $db->getResultSet('kk_mstr', ['kk_code'], ['kk_hq = "Y"']);
    $kkHQRow = $kkHQResult->fetch_assoc();
    $kkHQ = $kkHQRow['kk_code'];

    $tempDate = new Date($timeZone);
    $tempDate->subDays($days);

    $sundayDate = $tempDate->getMySQLFormat();
    $tempDate->addDays(1);
    $mondayDate = $tempDate->getMySQLFormat();
    $tempDate->addDays(1);
    $tuesdayDate = $tempDate->getMySQLFormat();
    $tempDate->addDays(1);
    $wednesdayDate = $tempDate->getMySQLFormat();
    $tempDate->addDays(1);
    $thursdayDate = $tempDate->getMySQLFormat();
    $tempDate->addDays(1);
    $fridayDate = $tempDate->getMySQLFormat();
    $tempDate->addDays(1);
    $saturdayDate = $tempDate->getMySQLFormat();
    $tempDate->addDays(1);
    $nextSundayDate = $tempDate->getMySQLFormat();
    $tempDate->addDays(1);
    $nextMondayDate = $tempDate->getMySQLFormat();
    $tempDate->addDays(1);
    $nextTuesdayDate = $tempDate->getMySQLFormat();
    $tempDate->addDays(1);
    $nextWednesdayDate = $tempDate->getMySQLFormat();
    $tempDate->addDays(1);
    $nextThursdayDate = $tempDate->getMySQLFormat();
    $tempDate->addDays(1);
    $nextFridayDate = $tempDate->getMySQLFormat();
    $tempDate->addDays(1);
    $nextSaturdayDate = $tempDate->getMySQLFormat();


    /*Get patient code from javascript and santify it before used in database*/
    /*$code = trim($_REQUEST['code']);
    $code = strtoupper($db->realEscapeString($code));*/

    /*Get all patient code in database*/
    $patientCodeResult = $db->getResultSet('patient_mstr', ['patient_code', 'patient_kk'], ['patient_active = "Y"'], ['patient_id'], ['patient_code']);

    while ($row = $patientCodeResult->fetch_assoc()) {
        $code = trim($row['patient_code']);
        $kk = trim($row['patient_kk']);

        /* Start ---- Auto create SPUB Out and SPUB In for SPUB Permanent*/

        $spubResult = $db->getResultSet('spubperm_mstr', 
            ['spubperm_code', 'spubperm_patcode', 'spubperm_kk', 'spubperm_sunday', 
            'spubperm_monday', 'spubperm_tuesday', 'spubperm_wednesday', 'spubperm_thursday', 
            'spubperm_friday', 'spubperm_saturday'], 
            ['spubperm_active = "Y"', '(spubperm_patcode = "' . $code . '" OR spubperm_code = "' . $code . '")']);
        while ($row = $spubResult->fetch_assoc()) {
            $sunday = $row['spubperm_sunday'];
            $monday = $row['spubperm_monday'];
            $tuesday = $row['spubperm_tuesday'];
            $wednesday = $row['spubperm_wednesday'];
            $thursday = $row['spubperm_thursday'];
            $friday = $row['spubperm_friday'];
            $saturday = $row['spubperm_saturday'];

            if (($todayDay == 'Sunday') && ($sunday == 'Y')) {
                $spubm1mExist = $db->isExist('patient_mstr', 
                    ['patient_active = "Y"', 
                    '((patient_code = "' . $row['spubperm_code'] . '" AND patient_spubin = "Y") OR (patient_code = "' . $row['spubperm_patcode'] . '" AND (patient_m1mout = "Y" OR patient_spubout = "Y")))']);

                // Check if any SPUB patient within interval of spubout supply date range
                $SPUBPatExist = $db->isExist('spubout_mstr', ["spubout_patcode = '$code'", "spubout_supplyfrom <= '$todayDate'", 
                    "spubout_supplyto >= '$todayDate'", "spubout_kk = '$kk'"]);

                if (!$spubm1mExist && !$SPUBPatExist) {
                    $list = getDateRange($sunday, $monday, $tuesday, $wednesday, $thursday, $friday, $saturday);
                }
            } else if (($todayDay == 'Monday') && ($monday == 'Y')) {
                $spubm1mExist = $db->isExist('patient_mstr', 
                    ['patient_active = "Y"', 
                    '((patient_code = "' . $row['spubperm_code'] . '" AND patient_spubin = "Y") OR (patient_code = "' . $row['spubperm_patcode'] . '" AND (patient_m1mout = "Y" OR patient_spubout = "Y")))']);

                // Check if any SPUB patient within interval of spubout supply date range
                $SPUBPatExist = $db->isExist('spubout_mstr', ["spubout_patcode = '$code'", "spubout_supplyfrom <= '$todayDate'", 
                    "spubout_supplyto >= '$todayDate'", "spubout_kk = '$kk'"]);

                if (!$spubm1mExist && !$SPUBPatExist) {
                    $list = getDateRange($sunday, $monday, $tuesday, $wednesday, $thursday, $friday, $saturday);
                }
            } else if (($todayDay == 'Tuesday') && ($tuesday == 'Y')) {
                $spubm1mExist = $db->isExist('patient_mstr', 
                    ['patient_active = "Y"', 
                    '((patient_code = "' . $row['spubperm_code'] . '" AND patient_spubin = "Y") OR (patient_code = "' . $row['spubperm_patcode'] . '" AND (patient_m1mout = "Y" OR patient_spubout = "Y")))']);

                // Check if any SPUB patient within interval of spubout supply date range
                $SPUBPatExist = $db->isExist('spubout_mstr', ["spubout_patcode = '$code'", "spubout_supplyfrom <= '$todayDate'", 
                    "spubout_supplyto >= '$todayDate'", "spubout_kk = '$kk'"]);

                if (!$spubm1mExist && !$SPUBPatExist) {
                    $list = getDateRange($sunday, $monday, $tuesday, $wednesday, $thursday, $friday, $saturday);
                }
            } else if (($todayDay == 'Wednesday') && ($wednesday == 'Y')) {
                $spubm1mExist = $db->isExist('patient_mstr', 
                    ['patient_active = "Y"', 
                    '((patient_code = "' . $row['spubperm_code'] . '" AND patient_spubin = "Y") OR (patient_code = "' . $row['spubperm_patcode'] . '" AND (patient_m1mout = "Y" OR patient_spubout = "Y")))']);

                // Check if any SPUB patient within interval of spubout supply date range
                $SPUBPatExist = $db->isExist('spubout_mstr', ["spubout_patcode = '$code'", "spubout_supplyfrom <= '$todayDate'", 
                    "spubout_supplyto >= '$todayDate'", "spubout_kk = '$kk'"]);

                if (!$spubm1mExist && !$SPUBPatExist) {
                    $list = getDateRange($sunday, $monday, $tuesday, $wednesday, $thursday, $friday, $saturday);
                }
            } else if (($todayDay == 'Thursday') && ($thursday == 'Y')) {
                $spubm1mExist = $db->isExist('patient_mstr', 
                    ['patient_active = "Y"', 
                    '((patient_code = "' . $row['spubperm_code'] . '" AND patient_spubin = "Y") OR (patient_code = "' . $row['spubperm_patcode'] . '" AND (patient_m1mout = "Y" OR patient_spubout = "Y")))']);

                // Check if any SPUB patient within interval of spubout supply date range
                $SPUBPatExist = $db->isExist('spubout_mstr', ["spubout_patcode = '$code'", "spubout_supplyfrom <= '$todayDate'", 
                    "spubout_supplyto >= '$todayDate'", "spubout_kk = '$kk'"]);

                if (!$spubm1mExist && !$SPUBPatExist) {
                    $list = getDateRange($sunday, $monday, $tuesday, $wednesday, $thursday, $friday, $saturday);
                }
            } else if (($todayDay == 'Friday') && ($friday == 'Y')) {
                $spubm1mExist = $db->isExist('patient_mstr', 
                    ['patient_active = "Y"', 
                    '((patient_code = "' . $row['spubperm_code'] . '" AND patient_spubin = "Y") OR (patient_code = "' . $row['spubperm_patcode'] . '" AND (patient_m1mout = "Y" OR patient_spubout = "Y")))']);

                // Check if any SPUB patient within interval of spubout supply date range
                $SPUBPatExist = $db->isExist('spubout_mstr', ["spubout_patcode = '$code'", "spubout_supplyfrom <= '$todayDate'", 
                    "spubout_supplyto >= '$todayDate'", "spubout_kk = '$kk'"]);

                if (!$spubm1mExist && !$SPUBPatExist) {
                    $list = getDateRange($sunday, $monday, $tuesday, $wednesday, $thursday, $friday, $saturday);
                }
            } else if (($todayDay == 'Saturday') && ($saturday == 'Y')) {
                $spubm1mExist = $db->isExist('patient_mstr', 
                    ['patient_active = "Y"', 
                    '((patient_code = "' . $row['spubperm_code'] . '" AND patient_spubin = "Y") OR (patient_code = "' . $row['spubperm_patcode'] . '" AND (patient_m1mout = "Y" OR patient_spubout = "Y")))']);

                // Check if any SPUB patient within interval of spubout supply date range
                $SPUBPatExist = $db->isExist('spubout_mstr', ["spubout_patcode = '$code'", "spubout_supplyfrom <= '$todayDate'", 
                    "spubout_supplyto >= '$todayDate'", "spubout_kk = '$kk'"]);

                if (!$spubm1mExist && !$SPUBPatExist) {
                    $list = getDateRange($sunday, $monday, $tuesday, $wednesday, $thursday, $friday, $saturday);
                }
            }      
        }

        /* End ---- Auto create SPUB Out and SPUB In for SPUB Permanent*/

        // Check if any SPUB patient within interval of spubout supply date range
        $SPUBPatExist = $db->isExist('spubout_mstr', ["spubout_patcode = '$code'", "spubout_supplyfrom <= '$todayDate'", 
            "spubout_supplyto >= '$todayDate'", "spubout_kk = '$kk'"]);

        /*Check whether patient is reactivate by today*/
        $todayReactivate = $db->isExist('patient_mstr',
            ["patient_code = '$code'", "patient_lastreactivate >= '$todayDate'", "patient_lastreactivate < '$tomorrowDate'", "patient_kk = '$kk'"]);

        // If exist, turn patient's status to SPUB OUT
        if ($SPUBPatExist && !$todayReactivate) {
            $updateStatus = $db->updateData('patient_mstr',['patient_status = "SPUB OUT"', 'patient_spubout = "Y"'], 
                ["patient_code = '$code'", "patient_kk = '$kk'"]);
        }

        /*Create two variables to hold patient's code and patient's SPUB Permanent code*/
        $spubPermExist = $db->isExist('spubperm_mstr', 
            ['spubperm_code = "' . $code . '"']);
        if ($spubPermExist) {
            $bothCodesResult = $db->getResultSet('spubperm_mstr', 
                ['spubperm_code', 'spubperm_patcode', 'spubperm_kk'], 
                ['spubperm_code = "' . $code . '" or spubperm_patcode = "' . $code . '"']);
            $bothCodesRow = $bothCodesResult->fetch_assoc();
            $scode = $bothCodesRow['spubperm_code'];
            $spatcode = $bothCodesRow['spubperm_patcode'];
            $skk = $bothCodesRow['spubperm_kk'];
        }

        /*Check patient's spub out status*/
        $patSPUBOut = $db->isExist('patient_mstr', ["patient_code = '$code'", "patient_status = 'SPUB OUT'", "patient_kk = '$kk'"]);

        /*If spub out, check whether patient already out of period*/
        if ($patSPUBOut) {

            /*Check in spubout_mstr whether patient already out of period*/
            $withinPeriod = $db->isExist('spubout_mstr', ['spubout_patcode = "' . $code . '"', 'spubout_supplyfrom <= "' . $todayDate . '"', 'spubout_supplyto >= "' . $todayDate . '"', 'spubout_kk = "' . $kk . '"']);
            $spubOutStatus = $db->isExist('patient_mstr', ["patient_code = '$code'", "patient_status = 'SPUB OUT'", "patient_kk = '$kk'"]);

            /*Update patient's status to NORMAL if patient out of period*/
            if (!$withinPeriod && $spubOutStatus) {
                $updateStatus = $db->updateData('patient_mstr',['patient_status = "NORMAL"', 'patient_lastreactivate = "' . $time . '"', 'patient_spubout = "N"'], 
                    ["patient_code = '$code'", "patient_kk = '$kk'"]);
            }
        }

        /*Check whether patient already attended for methadone*/
        $attended = $db->isExist('methascan_hist', 
            ["methascan_patcode = '$code'", "methascan_date >= '$todayDate'", "methascan_date < '$tomorrowDate'", 
            'methascan_kk = "' . $kk . '"', 'methascan_status = "Y"']);

        /*Get patient's name*/
        $patNameResult = $db->getResultSet('patient_mstr', ['patient_name'], 
            ['patient_code = "' . $code . '"', 'patient_active = "Y"', 'patient_kk = "' . $kk . '"']);
        $patNameRow = $patNameResult->fetch_assoc();
        $patname = $patNameRow['patient_name'];

        /*If patient not attend*/
        if (!$attended) {
            /*Determine whether patient is old patient*/
            if ($spubPermExist) {
                $oldPatient = $db->isExist('methascan_hist',
                    ["((methascan_patcode = '$scode' AND methascan_kk = '$kk') 
                        OR (methascan_patcode = '$spatcode' AND methascan_kk = '$skk'))", 
                    "methascan_date < '$tomorrowDate'", "methascan_status = 'Y'"]); 
            } else {
                $oldPatient = $db->isExist('methascan_hist',
                    ["methascan_patcode = '$code'", "methascan_date < '$tomorrowDate'", 
                    "methascan_kk = '$kk'", "methascan_status = 'Y'"]);    
            }

            /*3 days suspended checking only for old patient.*/
            if ($oldPatient) {
                /*Check whether patient is reactivate by today*/
                $todayReactivate = $db->isExist('patient_mstr',["patient_code = '$code'", "patient_lastreactivate >= '$todayDate'", "patient_lastreactivate < '$tomorrowDate'"]);

                /*System skip checking if patient reactivated by today, else check whether patient failed to attend in 3 days continuously */
                if (!$todayReactivate) {
                    if ($spubPermExist) {
                        $continuouslyAttd = $db->isExist('methascan_hist', 
                            ["((methascan_patcode = '$scode' AND methascan_kk = '$kk') 
                        OR (methascan_patcode = '$spatcode' AND methascan_kk = '$skk'))", 
                        "methascan_date >= '$prevFourDay'", 
                            "methascan_date < '$todayDate'", "methascan_status = 'Y'"]);                

                        $normalStatus = $db->isExist('patient_mstr', 
                            ["patient_code = '$scode'", "patient_status = 'SPUB IN'", "patient_kk = '$kk'", "patient_active = 'Y'", "patient_spubin = 'Y'"]);  

                        /*Update patient's status to SUSPENDED if patient failed to attend 3 days continuously*/
                        if (!$continuouslyAttd && $normalStatus) {
                            $updateStatus = $db->updateData('patient_mstr',['patient_status = "SUSPENDED"'], ["patient_code = '$scode'", "patient_kk = '$kk'"]);
                            $updateStatus = $db->updateData('patient_mstr',['patient_status = "SUSPENDED"'], ["patient_code = '$spatcode'", "patient_kk = '$skk'"]);
                            $suspendedStatus = $db->insertData('suspended_mstr',['suspended_patcode', 
                                    'suspended_patname', 'suspended_kk', 'user_created', 'date_created'],
                                    [$spatcode, $patname, $skk, 'admin', $time]);
                        }
                    } else {
                        $continuouslyAttd = $db->isExist('methascan_hist', 
                            ["methascan_patcode = '$code'", "methascan_date >= '$prevFourDay'", 
                            "methascan_date < '$todayDate'", "methascan_kk = '$kk'", "methascan_status = 'Y'"]);

                        $normalStatus = $db->isExist('patient_mstr', 
                            ["patient_code = '$code'", "patient_status = 'NORMAL'", "patient_kk = '$kk'"]);

                        /*Update patient's status to SUSPENDED if patient failed to attend 3 days continuously*/
                        if (!$continuouslyAttd && $normalStatus) {
                            $updateStatus = $db->updateData('patient_mstr',['patient_status = "SUSPENDED"'], 
                                ["patient_code = '$code'", "patient_kk = '$kk'"]);
                            $suspendedStatus = $db->insertData('suspended_mstr',
                                ['suspended_patcode', 'suspended_patname', 'suspended_kk', 'user_created', 
                                'date_created'],
                                [$code, $patname, $kk, 'admin', $time]);
                        }
                    }
                }
            }
        }

        /*Check patient's suspended status*/
        $suspended = $db->isExist('patient_mstr', ["patient_code = '$code'", "patient_status = 'SUSPENDED'", "patient_kk = '$kk'"]);

        /*If suspended check whether patient failed to attend in 2 weeks continuously*/
        if ($suspended) {
            /*Check whether patient is reactivate by today*/
            $todayReactivate = $db->isExist('patient_mstr',["patient_code = '$code'", "patient_lastreactivate >= '$todayDate'", "patient_lastreactivate < '$tomorrowDate'"]);

            /*System skip checking if patient reactivated by today, else check whether patient failed to attend in 2 weeks continuously */
            if (!$todayReactivate) {
                if ($spubPermExist) {
                    $continuouslyAttd = $db->isExist('methascan_hist', 
                        ["((methascan_patcode = '$scode' AND methascan_kk = '$kk') 
                    OR (methascan_patcode = '$spatcode' AND methascan_kk = '$skk'))", 
                    "methascan_date >= '$prevTwoWeek'", "methascan_date < '$todayDate'", 
                    "methascan_status = 'Y'"]);   

                    $suspendedStatus = $db->isExist('patient_mstr', 
                        ["patient_code = '$spatcode'", "patient_status = 'SUSPENDED'", "patient_kk = '$skk'", "patient_active = 'Y'"]);  

                    /*Update patient's status to LOST if patient failed to attend 2 weeks continuously*/
                    if (!$continuouslyAttd && $suspendedStatus) {
                        $updateStatus = $db->updateData('patient_mstr',['patient_status = "LOST"'], ["patient_code = '$scode'", "patient_kk = '$kk'"]);
                        $updateStatus = $db->updateData('patient_mstr',['patient_status = "LOST"'], ["patient_code = '$spatcode'", "patient_kk = '$skk'"]);
                        
                        /*Insert into lost_mstr*/
                        $lostStatus = $db->insertData('lost_mstr',['lost_patcode', 
                                    'lost_patname', 'lost_kk', 'user_created', 'date_created'],
                                    [$spatcode, $patname, $skk, 'admin', $time]);

                        /*Get suspended id*/
                        $result = $db->getResultSet('suspended_mstr', ['suspended_id'], 
                            ['suspended_patcode = "' . $spatcode . '"', 'suspended_active = "Y"', "suspended_kk = '$skk'"],
                            ['suspended_id'], ['suspended_id desc']);
                        $row = $result->fetch_assoc();
                        $suspendId = $row['suspended_id'];

                        /*Inactivate from suspended_mstr table*/
                        $updateStatus = $db->updateData('suspended_mstr',['suspended_active = "N"'], ["suspended_id = '$suspendId'", "suspended_kk = '$skk'"]);

                    }
                } else {
                    $continuouslyAttd = $db->isExist('methascan_hist', ["methascan_patcode = '$code'", "methascan_date >= '$prevTwoWeek'", "methascan_date < '$todayDate'", "methascan_kk = '$kk'"]);
                    $suspendedStatus = $db->isExist('patient_mstr', ["patient_code = '$code'", "patient_status = 'SUSPENDED'", "patient_kk = '$kk'"]);

                    /*Update patient's status to LOST if patient failed to attend 2 weeks continuously*/
                    if (!$continuouslyAttd && $suspendedStatus) {
                        $updateStatus = $db->updateData('patient_mstr',['patient_status = "LOST"'], ["patient_code = '$code'", "patient_kk = '$kk'"]);
                        
                        /*Insert into lost_mstr*/
                        $lostStatus = $db->insertData('lost_mstr',['lost_patcode', 
                                    'lost_patname', 'lost_kk', 'user_created', 'date_created'],
                                    [$code, $patname, $kk, 'admin', $time]);

                        /*Get suspended id*/
                        $result = $db->getResultSet('suspended_mstr', ['suspended_id'], 
                            ['suspended_patcode = "' . $code . '"', 'suspended_active = "Y"', "suspended_kk = '$kk'"],
                            ['suspended_id'], ['suspended_id desc']);
                        $row = $result->fetch_assoc();
                        $suspendId = $row['suspended_id'];

                        /*Inactivate from suspended_mstr table*/
                        $updateStatus = $db->updateData('suspended_mstr',['suspended_active = "N"'], ["suspended_id = '$suspendId'", "suspended_kk = '$kk'"]);
                    }
                }
            }
        }

        /*Check patient's lost status*/
        $lost = $db->isExist('patient_mstr', ["patient_code = '$code'", "patient_status = 'LOST'", "patient_kk = '$kk'"]);

        /*If lost check whether patient failed to attend in 1 month continuously*/
        if ($lost) {
            /*Check whether patient is reactivate by today*/
            $todayReactivate = $db->isExist('patient_mstr',["patient_code = '$code'", "patient_lastreactivate >= '$todayDate'", "patient_lastreactivate < '$tomorrowDate'"]);

            /*System skip checking if patient reactivated by today, else check whether patient failed to attend in 1 month continuously */
            if (!$todayReactivate) {
                if ($spubPermExist) {
                    $continuouslyAttd = $db->isExist('methascan_hist', 
                        ["((methascan_patcode = '$scode' AND methascan_kk = '$kk') 
                    OR (methascan_patcode = '$spatcode' AND methascan_kk = '$skk'))", 
                    "methascan_date >= '$prevOneMonth'", "methascan_date < '$todayDate'", 
                    "methascan_status = 'Y'"]);   

                    $suspendedStatus = $db->isExist('patient_mstr', 
                        ["patient_code = '$spatcode'", "patient_status = 'LOST'", "patient_kk = '$skk'", "patient_active = 'Y'"]);  

                    /*Update patient's status to LOST if patient failed to attend 2 weeks continuously*/
                    if (!$continuouslyAttd && $suspendedStatus) {
                        $updateStatus = $db->updateData('patient_mstr',['patient_status = "TERMINATED"'], ["patient_code = '$scode'", "patient_kk = '$kk'"]);
                        $updateStatus = $db->updateData('patient_mstr',['patient_status = "TERMINATED"'], ["patient_code = '$spatcode'", "patient_kk = '$skk'"]);
                        
                        /*Insert into terminated_mstr*/
                        $terminatedStatus = $db->insertData('terminated_mstr',['terminated_patcode', 
                                    'terminated_patname', 'terminated_kk', 'user_created', 'date_created'],
                                    [$spatcode, $patname, $skk, 'admin', $time]);

                        /*Get lost id*/
                        $result = $db->getResultSet('lost_mstr', ['lost_id'], 
                            ['lost_patcode = "' . $spatcode . '"', 'lost_active = "Y"', "lost_kk = '$skk'"],
                            ['lost_id'], ['lost_id desc']);
                        $row = $result->fetch_assoc();
                        $lostId = $row['lost_id'];

                        /*Inactivate from suspended_mstr table*/
                        $updateStatus = $db->updateData('lost_mstr',['lost_active = "N"'], ["lost_id = '$lostId'", "lost_kk = '$skk'"]);
                    }
                } else {
                    $continuouslyAttd = $db->isExist('methascan_hist', ["methascan_patcode = '$code'", "methascan_date >= '$prevOneMonth'", "methascan_date < '$todayDate'", "methascan_kk = '$kk'"]);
                    $lostStatus = $db->isExist('patient_mstr', ["patient_code = '$code'", "patient_status = 'LOST'", "patient_kk = '$kk'"]);

                    /*Update patient's status to TERMINATED if patient failed to attend 1 month continuously*/
                    if (!$continuouslyAttd && $lostStatus) {
                        $updateStatus = $db->updateData('patient_mstr',['patient_status = "TERMINATED"'], ["patient_code = '$code'", "patient_kk = '$kk'"]);

                        /*Insert into terminated_mstr*/
                        $terminatedStatus = $db->insertData('terminated_mstr',['terminated_patcode', 
                                    'terminated_patname', 'terminated_kk', 'user_created', 'date_created'],
                                    [$code, $patname, $kk, 'admin', $time]);

                        /*Get lost id*/
                        $result = $db->getResultSet('lost_mstr', ['lost_id'], 
                            ['lost_patcode = "' . $code . '"', 'lost_active = "Y"', "lost_kk = '$kk'"],
                            ['lost_id'], ['lost_id desc']);
                        $row = $result->fetch_assoc();
                        $lostId = $row['lost_id'];

                        /*Inactivate from suspended_mstr table*/
                        $updateStatus = $db->updateData('lost_mstr',['lost_active = "N"'], ["lost_id = '$lostId'", "lost_kk = '$kk'"]);

                    }
                }
            }
        }
    }

    function getDateRange($sunday, $monday, $tuesday, $wednesday, $thursday, $friday, $saturday) {
        global $sundayDate, $mondayDate, $tuesdayDate, $wednesdayDate, $thursdayDate, $fridayDate, $saturdayDate, 
        $nextSundayDate, $nextMondayDate, $nextTuesdayDate, $nextWednesdayDate, $nextThursdayDate, $nextFridayDate, $nextSaturdayDate;

        global $db, $row, $time, $kk, $kkHQ;

        $fromDate = $sundayDate;
        $toDate = $sundayDate;

        if (($sunday == 'Y') && ($monday == 'Y') && ($tuesday == 'Y') && ($wednesday == 'Y') 
            && ($thursday == 'Y') && ($friday == 'Y') && ($saturday == 'Y')) {
            $fromDate = $sundayDate;
            $toDate = $saturdayDate;
        } else if ($sunday == 'Y') {
            if ($saturday == 'Y') {
                $toDate = $nextSundayDate;

                if ($monday == 'Y') {
                    if ($sunday == 'N') {
                        $fromDate = $mondayDate;
                    } 

                    if($tuesday == 'N') {
                        $toDate = $nextMondayDate;
                    }
                }

                if ($tuesday == 'Y') {
                    if ($monday == 'N') {
                        $fromDate = $tuesdayDate;
                    } 

                    if($wednesday == 'N') {
                        $toDate = $nextTuesdayDate;
                    }
                }

                if ($wednesday == 'Y') {
                    if ($tuesday == 'N') {
                        $fromDate = $wednesdayDate;
                    } 

                    if($thursday == 'N') {
                        $toDate = $nextWednesdayDate;
                    }
                }

                if ($thursday == 'Y') {
                    if ($wednesday == 'N') {
                        $fromDate = $thursdayDate;
                    } 

                    if($friday == 'N') {
                        $toDate = $nextThursdayDate;
                    }
                }

                if ($friday == 'Y') {
                    if ($thursday == 'N') {
                        $fromDate = $fridayDate;
                    } 

                    if($saturday == 'N') {
                        $toDate = $nextFridayDate;
                    }
                }
            } else {
                if ($monday == 'Y') {
                    if ($tuesday == 'N') {
                        $toDate = $mondayDate;
                    }
                }

                if ($tuesday == 'Y') {
                    if ($wednesday == 'N') {
                        $toDate = $tuesdayDate;
                    }
                }

                if ($wednesday == 'Y') {
                    if ($thursday == 'N') {
                        $toDate = $wednesdayDate;
                    }
                }

                if ($thursday == 'Y') {
                    if ($tuesday == 'N') {
                        $toDate = $thursdayDate;
                    }
                }

                if ($friday == 'Y') {
                    if ($tuesday) {
                        $toDate = $fridayDate;
                    }
                }
            }
        } else {
            if ($monday == 'Y') {
                $fromDate = $mondayDate;
                $toDate = $mondayDate;
            }

            if ($tuesday == 'Y') {
                if ($monday == 'N') {
                    $fromDate = $tuesdayDate;
                }
                $toDate = $tuesdayDate;
            }

            if ($wednesday == 'Y') {
                if ($tuesday == 'N') {
                    $fromDate = $wednesdayDate;
                }
                $toDate = $wednesdayDate;
            }

            if ($thursday == 'Y') {
                if ($wednesday == 'N') {
                    $fromDate = $thursdayDate;
                }
                $toDate = $thursdayDate;
            }

            if ($friday == 'Y') {
                if ($thursday == 'N') {
                    $fromDate = $fridayDate;
                }
                $toDate = $fridayDate;
            }

            if ($saturday == 'Y') {
                if ($friday == 'N') {
                    $fromDate = $saturdayDate;
                }
                $toDate = $saturdayDate;
            }
        }

        $date1 = new DateTime($fromDate);
        $date2 = new DateTime($toDate);
        $dayDiff = $date1->diff($date2);
        $interval = (int) $dayDiff->format('%a') + 1;

        $infoResult = $db->getResultSet(['kk_mstr a', 'patient_mstr b'], 
            ['kk_state', 'kk_name', 'kk_addr1', 'kk_tel', 
            'kk_fax', 'patient_name', 'patient_code', 'patient_tel', 
            'patient_dose', 'patient_gender', 'patient_age', 'b.user_created'], 
            ['kk_code = patient_kk', 'patient_code = "' . $row['spubperm_patcode'] . '"', 
            'patient_active = "Y"', 'patient_kk = "' . $row['spubperm_kk'] . '"']);
        $infoRow = $infoResult->fetch_assoc();

        $patientSPUBInExist = $db->isExist('patient_mstr', ['patient_code = "' . $row['spubperm_code'] . '"']);
        
        $insertSPUBOutStatus = $db->insertData('spubout_mstr', 
            ['spubout_state', 'spubout_addr', 'spubout_fromkk', 'spubout_tel', 
            'spubout_fax', 'spubout_patname', 'spubout_patcode', 'spubout_patid',
            'spubout_age', 'spubout_gender', 'spubout_pattel', 'spubout_presid', 
            'spubout_presdate', 'spubout_presdose', 'spubout_supplyfrom', 'spubout_supplyto',
            'spubout_supplydays', 'spubout_active', 'spubout_pic', 'spubout_kk',
            'user_created', 'date_created'],
            [$infoRow['kk_state'], $infoRow['kk_addr1'], strtoupper($infoRow['kk_name']), $infoRow['kk_tel'],
            $infoRow['kk_fax'], $infoRow['patient_name'], $infoRow['patient_code'], $infoRow['patient_code'],
            $infoRow['patient_age'], $infoRow['patient_gender'], $infoRow['patient_tel'], '12345678',
            $fromDate, $infoRow['patient_dose'], $fromDate, $toDate,
            $interval, 'Y', strtoupper($infoRow['user_created']), $row['spubperm_kk'],
            'admin', $time]);
        $insertSPUBInStatus = $db->insertData('spubin_mstr', 
            ['spubin_state', 'spubin_addr', 'spubin_fromkk', 'spubin_tel', 
            'spubin_fax', 'spubin_patname', 'spubin_patcode', 'spubin_patid',
            'spubin_age', 'spubin_gender', 'spubin_pattel', 'spubin_presid', 
            'spubin_presdate', 'spubin_presdose', 'spubin_supplyfrom', 'spubin_supplyto',
            'spubin_supplydays', 'spubin_active', 'spubin_pic', 'spubin_kk',
            'user_created', 'date_created'],
            [$infoRow['kk_state'], $infoRow['kk_addr1'], strtoupper($infoRow['kk_name']), $infoRow['kk_tel'],
            $infoRow['kk_fax'], $infoRow['patient_name'], $row['spubperm_code'], $infoRow['patient_code'],
            $infoRow['patient_age'], $infoRow['patient_gender'], $infoRow['patient_tel'], '12345678',
            $fromDate, $infoRow['patient_dose'], $fromDate, $toDate,
            $interval, 'Y', strtoupper($infoRow['user_created']), $kkHQ,
            'admin', $time]);
        
        if ($patientSPUBInExist) {
            $updatePatientSPUBInStatus = $db->updateData('patient_mstr', 
                ['patient_status = "SPUB IN"', 'patient_spubin = "Y"', 'patient_active = "Y"', 'user_updated = "admin"', 
                'date_updated = "' . $time . '"'], 
                ['patient_code = "' . $row['spubperm_code'] . '"', 'patient_kk = "' . $kkHQ . '"']);
        } else {
            $volume = (int) $infoRow['patient_dose'] / 5;
            $insertPatientSPUBInStatus = $db->insertData('patient_mstr',
                ['patient_code', 'patient_name', 'patient_dose', 'patient_volume', 
                'patient_age', 'patient_gender', 'patient_status', 'patient_active', 
                'patient_mobile', 'patient_addr1', 'patient_kk', 'user_created', 
                'date_created', 'patient_spubin'],
                [$row['spubperm_code'], $infoRow['patient_name'], $infoRow['patient_dose'], $volume, 
                $infoRow['patient_age'], $infoRow['patient_gender'], 'SPUB IN', 'Y', 
                $infoRow['patient_tel'], strtoupper($infoRow['kk_name']), $kkHQ, 'admin', 
                $time, 'Y']);
        }
        

        $updateSPUBOutStatus = $db->updateData('patient_mstr', 
            ['patient_status = "SPUB OUT"', 'patient_spubout = "Y"', 'user_updated = "admin"', 'date_updated = "' . $time . '"'], 
            ['patient_code = "' . $row['spubperm_patcode'] . '"', 'patient_kk = "' . $row['spubperm_kk'] . '"']);

        // if ($updateSPUBOutStatus && $insertSPUBOutStatus && $insertSPUBInStatus && ($updatePatientSPUBInStatus || $insertPatientSPUBInStatus)) {
        //     echo "Success";
        // }

    }
    
?>
