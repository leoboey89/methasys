<?php
    require_once '../includes/session.php';
    require_once '../classes/Date.php';
    require_once '../classes/MySQLConnector.php';

    //Get day numbder of week
    date_default_timezone_set('Asia/Kuala_Lumpur');
    $day = date('w');

    /*Get first day of week*/
    $timeZone = new DateTimeZone('Asia/Kuala_Lumpur');
    $currentDate = new Date($timeZone);
    $todayDate = $currentDate->getMySQLFormat();
    $dayNameOfWeek = $currentDate->getDayName();
    $currentDate->subDays($day);
    $firstDayOfWeek = $currentDate->getMySQLFormat();
    $fromDate = $firstDayOfWeek;

    /*Get last day of week*/
    $nextDate = new Date($timeZone);
    $nextDate->addDays(7-$day);
    $lastDayOfWeek = $nextDate->getMySQLFormat();

    $db = new MySQLConnector('localhost', 'leoboey_db', 'methasys2015', 'leoboey_db');
    $db->getResultBySQL('delete from temp_mslist_hist');
    $db->getResultBySQL('delete from mslist_hist');
    $kk = $_COOKIE['kk'];

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {

        $fromDate = $_POST['date-selector'];
        $currentDate = new Date($timeZone);
        $dayNameOfWeek = $currentDate->getDayName();
        $currentDate->setFromMySQL($fromDate);
        $day = $currentDate->getDayNumber();
        $currentDate->subDays($day);
        $firstDayOfWeek = $currentDate->getMySQLFormat();

        /*Get last day of week*/
        $nextDate = new Date($timeZone);
        $nextDate->setFromMySQL($fromDate);
        $nextDate->addDays(7-$day);
        $lastDayOfWeek = $nextDate->getMySQLFormat();

        // Get patient daily records in time format of each patient
        $tempScanResult = $db->getResultBySQL('SELECT methascan_patcode, methascan_patname, methascan_dose, methascan_volume, 
            IF(DAYOFWEEK(CAST(methascan_date AS DATE)) = 1,TIME(methascan_date),"-") as "SUNDAY", 
            IF(DAYOFWEEK(CAST(methascan_date AS DATE)) = 2,TIME(methascan_date),"-") as "MONDAY", 
            IF(DAYOFWEEK(CAST(methascan_date AS DATE)) = 3,TIME(methascan_date),"-") as "TUESDAY", 
            IF(DAYOFWEEK(CAST(methascan_date AS DATE)) = 4,TIME(methascan_date),"-") as "WEDNESDAY", 
            IF(DAYOFWEEK(CAST(methascan_date AS DATE)) = 5,TIME(methascan_date),"-") as "THURSDAY", 
            IF(DAYOFWEEK(CAST(methascan_date AS DATE)) = 6,TIME(methascan_date),"-") as "FRIDAY", 
            IF(DAYOFWEEK(CAST(methascan_date AS DATE)) = 7,TIME(methascan_date),"-") as "SATURDAY" 
            FROM methascan_hist WHERE methascan_status = "Y" AND methascan_date >= "' . $firstDayOfWeek . '" 
            AND methascan_date < "' . $lastDayOfWeek . '" AND methascan_dot = "Y"
            AND methascan_kk = "' . $kk . '" 
            UNION 
            SELECT methascan_patcode, methascan_patname, methascan_dose, methascan_volume, 
            IF(DAYOFWEEK(CAST(methascan_date AS DATE)) = 1,"dbb","-") as "SUNDAY", 
            IF(DAYOFWEEK(CAST(methascan_date AS DATE)) = 2,"dbb","-") as "MONDAY", 
            IF(DAYOFWEEK(CAST(methascan_date AS DATE)) = 3,"dbb","-") as "TUESDAY", 
            IF(DAYOFWEEK(CAST(methascan_date AS DATE)) = 4,"dbb","-") as "WEDNESDAY", 
            IF(DAYOFWEEK(CAST(methascan_date AS DATE)) = 5,"dbb","-") as "THURSDAY", 
            IF(DAYOFWEEK(CAST(methascan_date AS DATE)) = 6,"dbb","-") as "FRIDAY", 
            IF(DAYOFWEEK(CAST(methascan_date AS DATE)) = 7,"dbb","-") as "SATURDAY" 
            FROM methascan_hist WHERE methascan_status = "Y" AND methascan_date >= "' . $firstDayOfWeek . '"
            AND methascan_date < "' . $lastDayOfWeek . '" AND methascan_dbb = "Y"
            AND methascan_kk = "' . $kk . '" 
            GROUP BY methascan_patcode, methascan_date');

        // Insert into temp_mslist_hist into single row of record per patient code
        while ($row = $tempScanResult->fetch_assoc()) {
            $recordExist = $db->isExist('temp_mslist_hist', ['mslist_patcode = "' . $row['methascan_patcode'] . '"']);
            if (!$recordExist) {
                if ($row['SUNDAY'] != "-") {
                    $db->insertData('temp_mslist_hist', ['mslist_patcode','mslist_patname','mslist_dose','mslist_volume','mslist_sunday'], 
                    [$row['methascan_patcode'],$row['methascan_patname'],$row['methascan_dose'],$row['methascan_volume'],$row['SUNDAY'], ]);
                } else if ($row['MONDAY'] != "-") {
                    $db->insertData('temp_mslist_hist', ['mslist_patcode','mslist_patname','mslist_dose','mslist_volume','mslist_monday'], 
                    [$row['methascan_patcode'],$row['methascan_patname'],$row['methascan_dose'],$row['methascan_volume'],$row['MONDAY']]);
                } else if ($row['TUESDAY'] != "-") {
                    $db->insertData('temp_mslist_hist', ['mslist_patcode','mslist_patname','mslist_dose','mslist_volume','mslist_tuesday'], 
                    [$row['methascan_patcode'],$row['methascan_patname'],$row['methascan_dose'],$row['methascan_volume'],$row['TUESDAY']]);
                } else if ($row['WEDNESDAY'] != "-") {
                    $db->insertData('temp_mslist_hist', ['mslist_patcode','mslist_patname','mslist_dose','mslist_volume','mslist_wednesday'], 
                    [$row['methascan_patcode'],$row['methascan_patname'],$row['methascan_dose'],$row['methascan_volume'],$row['WEDNESDAY']]);
                } else if ($row['THURSDAY'] != "-") {
                    $db->insertData('temp_mslist_hist', ['mslist_patcode','mslist_patname','mslist_dose','mslist_volume','mslist_thursday'], 
                    [$row['methascan_patcode'],$row['methascan_patname'],$row['methascan_dose'],$row['methascan_volume'],$row['THURSDAY']]);
                } else if ($row['FRIDAY'] != "-") {
                    $db->insertData('temp_mslist_hist', ['mslist_patcode','mslist_patname','mslist_dose','mslist_volume','mslist_friday'], 
                    [$row['methascan_patcode'],$row['methascan_patname'],$row['methascan_dose'],$row['methascan_volume'],$row['FRIDAY']]);
                } else if ($row['SATURDAY'] != "-") {
                    $db->insertData('temp_mslist_hist', ['mslist_patcode','mslist_patname','mslist_dose','mslist_volume','mslist_saturday'], 
                    [$row['methascan_patcode'],$row['methascan_patname'],$row['methascan_dose'],$row['methascan_volume'],$row['SATURDAY']]);
                }
                
            } else {
                if ($row['SUNDAY'] != "-") {
                    $db->updateData('temp_mslist_hist', ['mslist_sunday = "'. $row['SUNDAY'] .'"'],
                        ['mslist_patcode = "' . $row['methascan_patcode'] . '"']);
                } else if ($row['MONDAY'] != "-") {
                    $db->updateData('temp_mslist_hist', ['mslist_monday = "'. $row['MONDAY'] .'"'],
                        ['mslist_patcode = "' . $row['methascan_patcode'] . '"']);
                } else if ($row['TUESDAY'] != "-") {
                    $db->updateData('temp_mslist_hist', ['mslist_tuesday = "'. $row['TUESDAY'] .'"'],
                        ['mslist_patcode = "' . $row['methascan_patcode'] . '"']);
                } else if ($row['WEDNESDAY'] != "-") {
                    $db->updateData('temp_mslist_hist', ['mslist_wednesday = "'. $row['WEDNESDAY'] .'"'],
                        ['mslist_patcode = "' . $row['methascan_patcode'] . '"']);
                } else if ($row['THURSDAY'] != "-") {
                    $db->updateData('temp_mslist_hist', ['mslist_thursday = "'. $row['THURSDAY'] .'"'],
                        ['mslist_patcode = "' . $row['methascan_patcode'] . '"']);
                } else if ($row['FRIDAY'] != "-") {
                    $db->updateData('temp_mslist_hist', ['mslist_friday = "'. $row['FRIDAY'] .'"'],
                        ['mslist_patcode = "' . $row['methascan_patcode'] . '"']);
                } else if ($row['SATURDAY'] != "-") {
                    $db->updateData('temp_mslist_hist', ['mslist_saturday = "'. $row['SATURDAY'] .'"'],
                        ['mslist_patcode = "' . $row['methascan_patcode'] . '"']);
                }
            }
        }

        // Insert all records to mslist_hist for displaying purpose, at this point it will update some info
        $db->getResultBySQL('INSERT INTO mslist_hist SELECT patient_code as mslist_patcode, patient_name as mslist_patname, 
            patient_dose as mslist_dose, patient_volume as mslist_volume, 
            IFNULL(mslist_sunday, "-") as mslist_sunday, IFNULL(mslist_monday, "-") as mslist_monday, 
            IFNULL(mslist_tuesday, "-") as mslist_tuesday, IFNULL(mslist_wednesday, "-") as mslist_wednesday, 
            IFNULL(mslist_thursday, "-") as mslist_thursday, IFNULL(mslist_friday, "-") as mslist_friday, 
            IFNULL(mslist_saturday, "-") as mslist_saturday, patient_spubout as mslist_spubout, 
            patient_spubin as mslist_spubin, patient_m1mout as mslist_m1mout, patient_m1min as mslist_m1min 
            from patient_mstr LEFT JOIN temp_mslist_hist
            ON mslist_patcode = patient_code WHERE patient_kk = "' . $kk . '" ORDER BY patient_code');

        // Check if any spubout record in database that matched with selected week range
        $SPUBPatResult = $db->getResultBySQL('SELECT * FROM spubout_mstr 
            WHERE (spubout_supplyfrom >= "' . $firstDayOfWeek . '" AND spubout_supplyfrom < "' . $lastDayOfWeek . '") 
            OR (spubout_supplyto >= "' . $firstDayOfWeek . '" AND spubout_supplyto < "' . $lastDayOfWeek . '")
            AND spubout_kk = "' . $kk . '"');

        // If there is record of spubout, it will update selected day with "spubout" to indicate user
        while ($row = $SPUBPatResult->fetch_assoc()) {
            $dateFrom = new DateTime($row['spubout_supplyfrom']);
            $dateTo = new DateTime($row['spubout_supplyto']);
            $dateTo->modify('+1 day');
            $minDate = $dateFrom->format('Y-m-d');
            $maxDate = $dateTo->format('Y-m-d');
            
            if ($minDate < $firstDayOfWeek) {
                $minDate = $firstDayOfWeek;
            }

            if ($maxDate > $lastDayOfWeek) {
                $maxDate = $lastDayOfWeek;
            }

            while ($minDate < $maxDate) {
                $row['spubout_patcode'];
                $dayNameFormat = new DateTime($minDate);
                $dayName = $dayNameFormat->format('l');

                switch ($dayName) {
                    case 'Sunday':
                        $db->getResultBySQL('UPDATE mslist_hist SET mslist_sunday = "spubout" 
                            WHERE mslist_patcode = "' . $row['spubout_patcode'] . '"');
                        break;
                    case 'Monday':
                        $db->getResultBySQL('UPDATE mslist_hist SET mslist_monday = "spubout" 
                            WHERE mslist_patcode = "' . $row['spubout_patcode'] . '"');
                        break;
                    case 'Tuesday':
                        $db->getResultBySQL('UPDATE mslist_hist SET mslist_tuesday = "spubout" 
                            WHERE mslist_patcode = "' . $row['spubout_patcode'] . '"');
                        break;
                    case 'Wednesday':
                        $db->getResultBySQL('UPDATE mslist_hist SET mslist_wednesday = "spubout" 
                            WHERE mslist_patcode = "' . $row['spubout_patcode'] . '"');
                        break;
                    case 'Thursday':
                        $db->getResultBySQL('UPDATE mslist_hist SET mslist_thursday = "spubout" 
                            WHERE mslist_patcode = "' . $row['spubout_patcode'] . '"');
                        break;
                    case 'Friday':
                        $db->getResultBySQL('UPDATE mslist_hist SET mslist_friday = "spubout" 
                            WHERE mslist_patcode = "' . $row['spubout_patcode'] . '"');
                        break;
                    case 'Saturday':
                        $db->getResultBySQL('UPDATE mslist_hist SET mslist_saturday = "spubout" 
                            WHERE mslist_patcode = "' . $row['spubout_patcode'] . '"');
                        break;
                    default:
                        break;
                }
                $addMinDate = new DateTime($minDate);
                $addMinDate->modify('+1 day');
                $minDate = $addMinDate->format('Y-m-d');
            }
        }

        // Check if any spubout record in database that matched with selected week range
        $M1MPatResult = $db->getResultBySQL('SELECT * FROM m1mout_mstr 
            WHERE (m1mout_dateout >= "' . $firstDayOfWeek . '" AND m1mout_dateout < "' . $lastDayOfWeek . '") 
            OR (m1mout_datein >= "' . $firstDayOfWeek . '" AND m1mout_datein < "' . $lastDayOfWeek . '")
            AND m1mout_kk = "' . $kk . '"');

        // If there is record of spubout, it will update selected day with "spubout" to indicate user
        while ($row = $M1MPatResult->fetch_assoc()) {
            $dateFrom = new DateTime($row['m1mout_dateout']);
            $dateTo = new DateTime($row['m1mout_datein']);
            $dateTo->modify('+1 day');
            $minDate = $dateFrom->format('Y-m-d');
            $maxDate = $dateTo->format('Y-m-d');
            
            if ($minDate < $firstDayOfWeek) {
                $minDate = $firstDayOfWeek;
            }

            if ($maxDate > $lastDayOfWeek) {
                $maxDate = $lastDayOfWeek;
            }

            while ($minDate < $maxDate) {
                $row['m1mout_patcode'];
                $dayNameFormat = new DateTime($minDate);
                $dayName = $dayNameFormat->format('l');
                $dayNameFormat->format('Y-m-d');

                switch ($dayName) {
                    case 'Sunday':
                        $db->getResultBySQL('UPDATE mslist_hist SET mslist_sunday = "m1mout" 
                            WHERE mslist_patcode = "' . $row['m1mout_patcode'] . '"');
                        break;
                    case 'Monday':
                        $db->getResultBySQL('UPDATE mslist_hist SET mslist_monday = "m1mout" 
                            WHERE mslist_patcode = "' . $row['m1mout_patcode'] . '"');
                        break;
                    case 'Tuesday':
                        $db->getResultBySQL('UPDATE mslist_hist SET mslist_tuesday = "m1mout" 
                            WHERE mslist_patcode = "' . $row['m1mout_patcode'] . '"');
                        break;
                    case 'Wednesday':
                        $db->getResultBySQL('UPDATE mslist_hist SET mslist_wednesday = "m1mout" 
                            WHERE mslist_patcode = "' . $row['m1mout_patcode'] . '"');
                        break;
                    case 'Thursday':
                        $db->getResultBySQL('UPDATE mslist_hist SET mslist_thursday = "m1mout" 
                            WHERE mslist_patcode = "' . $row['m1mout_patcode'] . '"');
                        break;
                    case 'Friday':
                        $db->getResultBySQL('UPDATE mslist_hist SET mslist_friday = "m1mout" 
                            WHERE mslist_patcode = "' . $row['m1mout_patcode'] . '"');
                        break;
                    case 'Saturday':
                        $db->getResultBySQL('UPDATE mslist_hist SET mslist_saturday = "m1mout" 
                            WHERE mslist_patcode = "' . $row['m1mout_patcode'] . '"');
                        break;
                    default:
                        break;
                }
                $addMinDate = new DateTime($minDate);
                $addMinDate->modify('+1 day');
                $minDate = $addMinDate->format('Y-m-d');
            }
        }

        // Logics that make report look nicer, for displaying "-" and "TH"
        $datetime1 = new DateTime($firstDayOfWeek);
        $datetime2 = new DateTime($todayDate);
        $interval = $datetime2->diff($datetime1);
        $dayDiff = (int) $interval->format("%R%a"); 

        if ($dayDiff < -6) {
            $db->getResultBySQL('UPDATE mslist_hist SET mslist_sunday = "TH" WHERE mslist_sunday = "-"');
            $db->getResultBySQL('UPDATE mslist_hist SET mslist_monday = "TH" WHERE mslist_monday = "-"');
            $db->getResultBySQL('UPDATE mslist_hist SET mslist_tuesday = "TH" WHERE mslist_tuesday = "-"');
            $db->getResultBySQL('UPDATE mslist_hist SET mslist_wednesday = "TH" WHERE mslist_wednesday = "-"');
            $db->getResultBySQL('UPDATE mslist_hist SET mslist_thursday = "TH" WHERE mslist_thursday = "-"');
            $db->getResultBySQL('UPDATE mslist_hist SET mslist_friday = "TH" WHERE mslist_friday = "-"');
            $db->getResultBySQL('UPDATE mslist_hist SET mslist_saturday = "TH" WHERE mslist_saturday = "-"');
        } else if ($dayDiff > 0) {
            $db->getResultBySQL('UPDATE mslist_hist SET mslist_sunday = "-" WHERE mslist_sunday <> "dbb"');
            $db->getResultBySQL('UPDATE mslist_hist SET mslist_monday = "-" WHERE mslist_monday <> "dbb"');
            $db->getResultBySQL('UPDATE mslist_hist SET mslist_tuesday = "-" WHERE mslist_tuesday <> "dbb"');
            $db->getResultBySQL('UPDATE mslist_hist SET mslist_wednesday = "-" WHERE mslist_wednesday <> "dbb"');
            $db->getResultBySQL('UPDATE mslist_hist SET mslist_thursday = "-" WHERE mslist_thursday <> "dbb"');
            $db->getResultBySQL('UPDATE mslist_hist SET mslist_friday = "-" WHERE mslist_friday <> "dbb"');
            $db->getResultBySQL('UPDATE mslist_hist SET mslist_saturday = "-" WHERE mslist_saturday <> "dbb"');
        } else {
            switch ($dayNameOfWeek) {
                case 'Sunday':
                    $db->getResultBySQL('UPDATE mslist_hist SET mslist_sunday = "TH" WHERE mslist_sunday = "-"');
                    $db->getResultBySQL('UPDATE mslist_hist SET mslist_monday = "-" WHERE mslist_monday <> "dbb" AND mslist_monday <> "spubout" AND mslist_monday <> "m1mout"');
                    $db->getResultBySQL('UPDATE mslist_hist SET mslist_tuesday = "-" WHERE mslist_tuesday <> "dbb" AND mslist_tuesday <> "spubout" AND mslist_tuesday <> "m1mout"');
                    $db->getResultBySQL('UPDATE mslist_hist SET mslist_wednesday = "-" WHERE mslist_wednesday <> "dbb" AND mslist_wednesday <> "spubout" AND mslist_wednesday <> "m1mout"');
                    $db->getResultBySQL('UPDATE mslist_hist SET mslist_thursday = "-" WHERE mslist_thursday <> "dbb" AND mslist_thursday <> "spubout" AND mslist_thursday <> "m1mout"');
                    $db->getResultBySQL('UPDATE mslist_hist SET mslist_friday = "-" WHERE mslist_friday <> "dbb" AND mslist_friday <> "spubout" AND mslist_friday <> "m1mout"');
                    $db->getResultBySQL('UPDATE mslist_hist SET mslist_saturday = "-" WHERE mslist_saturday <> "dbb" AND mslist_saturday <> "spubout" AND mslist_saturday <> "m1mout"');
                    break;
                case 'Monday':
                    $db->getResultBySQL('UPDATE mslist_hist SET mslist_sunday = "TH" WHERE mslist_sunday = "-"');
                    $db->getResultBySQL('UPDATE mslist_hist SET mslist_monday = "TH" WHERE mslist_monday = "-"');
                    $db->getResultBySQL('UPDATE mslist_hist SET mslist_tuesday = "-" WHERE mslist_tuesday <> "dbb" AND mslist_tuesday <> "spubout" AND mslist_tuesday <> "m1mout"');
                    $db->getResultBySQL('UPDATE mslist_hist SET mslist_wednesday = "-" WHERE mslist_wednesday <> "dbb" AND mslist_wednesday <> "spubout" AND mslist_wednesday <> "m1mout"');
                    $db->getResultBySQL('UPDATE mslist_hist SET mslist_thursday = "-" WHERE mslist_thursday <> "dbb" AND mslist_thursday <> "spubout" AND mslist_thursday <> "m1mout"');
                    $db->getResultBySQL('UPDATE mslist_hist SET mslist_friday = "-" WHERE mslist_friday <> "dbb" AND mslist_friday <> "spubout" AND mslist_friday <> "m1mout"');
                    $db->getResultBySQL('UPDATE mslist_hist SET mslist_saturday = "-" WHERE mslist_saturday <> "dbb" AND mslist_saturday <> "spubout" AND mslist_saturday <> "m1mout"');
                    break;
                case 'Tuesday':
                    $db->getResultBySQL('UPDATE mslist_hist SET mslist_sunday = "TH" WHERE mslist_sunday = "-"');
                    $db->getResultBySQL('UPDATE mslist_hist SET mslist_monday = "TH" WHERE mslist_monday = "-"');
                    $db->getResultBySQL('UPDATE mslist_hist SET mslist_tuesday = "TH" WHERE mslist_tuesday = "-"');
                    $db->getResultBySQL('UPDATE mslist_hist SET mslist_wednesday = "-" WHERE mslist_wednesday <> "dbb" AND mslist_wednesday <> "spubout" AND mslist_wednesday <> "m1mout"');
                    $db->getResultBySQL('UPDATE mslist_hist SET mslist_thursday = "-" WHERE mslist_thursday <> "dbb" AND mslist_thursday <> "spubout" AND mslist_thursday <> "m1mout"');
                    $db->getResultBySQL('UPDATE mslist_hist SET mslist_friday = "-" WHERE mslist_friday <> "dbb" AND mslist_friday <> "spubout" AND mslist_friday <> "m1mout"');
                    $db->getResultBySQL('UPDATE mslist_hist SET mslist_saturday = "-" WHERE mslist_saturday <> "dbb" AND mslist_saturday <> "spubout" AND mslist_saturday <> "m1mout"');
                    break;
                case 'Wednesday':
                    $db->getResultBySQL('UPDATE mslist_hist SET mslist_sunday = "TH" WHERE mslist_sunday = "-"');
                    $db->getResultBySQL('UPDATE mslist_hist SET mslist_monday = "TH" WHERE mslist_monday = "-"');
                    $db->getResultBySQL('UPDATE mslist_hist SET mslist_tuesday = "TH" WHERE mslist_tuesday = "-"');
                    $db->getResultBySQL('UPDATE mslist_hist SET mslist_wednesday = "TH" WHERE mslist_wednesday = "-"');
                    $db->getResultBySQL('UPDATE mslist_hist SET mslist_thursday = "-" WHERE mslist_thursday <> "dbb" AND mslist_thursday <> "spubout" AND mslist_thursday <> "m1mout"');
                    $db->getResultBySQL('UPDATE mslist_hist SET mslist_friday = "-" WHERE mslist_friday <> "dbb" AND mslist_friday <> "spubout" AND mslist_friday <> "m1mout"');
                    $db->getResultBySQL('UPDATE mslist_hist SET mslist_saturday = "-" WHERE mslist_saturday <> "dbb" AND mslist_saturday <> "spubout" AND mslist_saturday <> "m1mout"');
                    break;
                case 'Thursday':
                    $db->getResultBySQL('UPDATE mslist_hist SET mslist_sunday = "TH" WHERE mslist_sunday = "-"');
                    $db->getResultBySQL('UPDATE mslist_hist SET mslist_monday = "TH" WHERE mslist_monday = "-"');
                    $db->getResultBySQL('UPDATE mslist_hist SET mslist_tuesday = "TH" WHERE mslist_tuesday = "-"');
                    $db->getResultBySQL('UPDATE mslist_hist SET mslist_wednesday = "TH" WHERE mslist_wednesday = "-"');
                    $db->getResultBySQL('UPDATE mslist_hist SET mslist_thursday = "TH" WHERE mslist_thursday = "-"');
                    $db->getResultBySQL('UPDATE mslist_hist SET mslist_friday = "-" WHERE mslist_friday <> "dbb" AND mslist_friday <> "spubout" AND mslist_friday <> "m1mout"');
                    $db->getResultBySQL('UPDATE mslist_hist SET mslist_saturday = "-" WHERE mslist_saturday <> "dbb" AND mslist_saturday <> "spubout" AND mslist_saturday <> "m1mout"');
                    break;
                case 'Friday':
                    $db->getResultBySQL('UPDATE mslist_hist SET mslist_sunday = "TH" WHERE mslist_sunday = "-"');
                    $db->getResultBySQL('UPDATE mslist_hist SET mslist_monday = "TH" WHERE mslist_monday = "-"');
                    $db->getResultBySQL('UPDATE mslist_hist SET mslist_tuesday = "TH" WHERE mslist_tuesday = "-"');
                    $db->getResultBySQL('UPDATE mslist_hist SET mslist_wednesday = "TH" WHERE mslist_wednesday = "-"');
                    $db->getResultBySQL('UPDATE mslist_hist SET mslist_thursday = "TH" WHERE mslist_thursday = "-"');
                    $db->getResultBySQL('UPDATE mslist_hist SET mslist_friday = "TH" WHERE mslist_friday = "-"');
                    $db->getResultBySQL('UPDATE mslist_hist SET mslist_saturday = "-" WHERE mslist_saturday <> "dbb" AND mslist_saturday <> "spubout" AND mslist_saturday <> "m1mout"');
                    break;
                case 'Saturday':
                    $db->getResultBySQL('UPDATE mslist_hist SET mslist_sunday = "TH" WHERE mslist_sunday = "-"');
                    $db->getResultBySQL('UPDATE mslist_hist SET mslist_monday = "TH" WHERE mslist_monday = "-"');
                    $db->getResultBySQL('UPDATE mslist_hist SET mslist_tuesday = "TH" WHERE mslist_tuesday = "-"');
                    $db->getResultBySQL('UPDATE mslist_hist SET mslist_wednesday = "TH" WHERE mslist_wednesday = "-"');
                    $db->getResultBySQL('UPDATE mslist_hist SET mslist_thursday = "TH" WHERE mslist_thursday = "-"');
                    $db->getResultBySQL('UPDATE mslist_hist SET mslist_friday = "TH" WHERE mslist_friday = "-"');
                    $db->getResultBySQL('UPDATE mslist_hist SET mslist_saturday = "TH" WHERE mslist_saturday = "-"');
                    break;
                default:
                    break;
            }
        }
        
        // Get final result for display complete master list
        $scanResult = $db->getResultBySQL('SELECT * from mslist_hist');
    } else {
        // Get patient daily records in time format of each patient
        $tempScanResult = $db->getResultBySQL('SELECT methascan_patcode, methascan_patname, methascan_dose, methascan_volume, 
            IF(DAYOFWEEK(CAST(methascan_date AS DATE)) = 1,TIME(methascan_date),"-") as "SUNDAY", 
            IF(DAYOFWEEK(CAST(methascan_date AS DATE)) = 2,TIME(methascan_date),"-") as "MONDAY", 
            IF(DAYOFWEEK(CAST(methascan_date AS DATE)) = 3,TIME(methascan_date),"-") as "TUESDAY", 
            IF(DAYOFWEEK(CAST(methascan_date AS DATE)) = 4,TIME(methascan_date),"-") as "WEDNESDAY", 
            IF(DAYOFWEEK(CAST(methascan_date AS DATE)) = 5,TIME(methascan_date),"-") as "THURSDAY", 
            IF(DAYOFWEEK(CAST(methascan_date AS DATE)) = 6,TIME(methascan_date),"-") as "FRIDAY", 
            IF(DAYOFWEEK(CAST(methascan_date AS DATE)) = 7,TIME(methascan_date),"-") as "SATURDAY" 
            FROM methascan_hist WHERE methascan_status = "Y" AND methascan_date >= "' . $firstDayOfWeek . '" 
            AND methascan_date < "' . $lastDayOfWeek . '" AND methascan_dot = "Y"
            AND methascan_kk = "' . $kk . '" 
            UNION 
            SELECT methascan_patcode, methascan_patname, methascan_dose, methascan_volume, 
            IF(DAYOFWEEK(CAST(methascan_date AS DATE)) = 1,"dbb","-") as "SUNDAY", 
            IF(DAYOFWEEK(CAST(methascan_date AS DATE)) = 2,"dbb","-") as "MONDAY", 
            IF(DAYOFWEEK(CAST(methascan_date AS DATE)) = 3,"dbb","-") as "TUESDAY", 
            IF(DAYOFWEEK(CAST(methascan_date AS DATE)) = 4,"dbb","-") as "WEDNESDAY", 
            IF(DAYOFWEEK(CAST(methascan_date AS DATE)) = 5,"dbb","-") as "THURSDAY", 
            IF(DAYOFWEEK(CAST(methascan_date AS DATE)) = 6,"dbb","-") as "FRIDAY", 
            IF(DAYOFWEEK(CAST(methascan_date AS DATE)) = 7,"dbb","-") as "SATURDAY" 
            FROM methascan_hist WHERE methascan_status = "Y" AND methascan_date >= "' . $firstDayOfWeek . '"
            AND methascan_date < "' . $lastDayOfWeek . '" AND methascan_dbb = "Y"
            AND methascan_kk = "' . $kk . '" 
            GROUP BY methascan_patcode, methascan_date');

        // Insert into temp_mslist_hist into single row of record per patient code
        while ($row = $tempScanResult->fetch_assoc()) {
            $recordExist = $db->isExist('temp_mslist_hist', ['mslist_patcode = "' . $row['methascan_patcode'] . '"']);
            if (!$recordExist) {
                if ($row['SUNDAY'] != "-") {
                    $db->insertData('temp_mslist_hist', ['mslist_patcode','mslist_patname','mslist_dose','mslist_volume','mslist_sunday'], 
                    [$row['methascan_patcode'],$row['methascan_patname'],$row['methascan_dose'],$row['methascan_volume'],$row['SUNDAY']]);
                } else if ($row['MONDAY'] != "-") {
                    $db->insertData('temp_mslist_hist', ['mslist_patcode','mslist_patname','mslist_dose','mslist_volume','mslist_monday'], 
                    [$row['methascan_patcode'],$row['methascan_patname'],$row['methascan_dose'],$row['methascan_volume'],$row['MONDAY']]);
                } else if ($row['TUESDAY'] != "-") {
                    $db->insertData('temp_mslist_hist', ['mslist_patcode','mslist_patname','mslist_dose','mslist_volume','mslist_tuesday'], 
                    [$row['methascan_patcode'],$row['methascan_patname'],$row['methascan_dose'],$row['methascan_volume'],$row['TUESDAY']]);
                } else if ($row['WEDNESDAY'] != "-") {
                    $db->insertData('temp_mslist_hist', ['mslist_patcode','mslist_patname','mslist_dose','mslist_volume','mslist_wednesday'], 
                    [$row['methascan_patcode'],$row['methascan_patname'],$row['methascan_dose'],$row['methascan_volume'],$row['WEDNESDAY']]);
                } else if ($row['THURSDAY'] != "-") {
                    $db->insertData('temp_mslist_hist', ['mslist_patcode','mslist_patname','mslist_dose','mslist_volume','mslist_thursday'], 
                    [$row['methascan_patcode'],$row['methascan_patname'],$row['methascan_dose'],$row['methascan_volume'],$row['THURSDAY']]);
                } else if ($row['FRIDAY'] != "-") {
                    $db->insertData('temp_mslist_hist', ['mslist_patcode','mslist_patname','mslist_dose','mslist_volume','mslist_friday'], 
                    [$row['methascan_patcode'],$row['methascan_patname'],$row['methascan_dose'],$row['methascan_volume'],$row['FRIDAY']]);
                } else if ($row['SATURDAY'] != "-") {
                    $db->insertData('temp_mslist_hist', ['mslist_patcode','mslist_patname','mslist_dose','mslist_volume','mslist_saturday'], 
                    [$row['methascan_patcode'],$row['methascan_patname'],$row['methascan_dose'],$row['methascan_volume'],$row['SATURDAY']]);
                }
                
            } else {
                if ($row['SUNDAY'] != "-") {
                    $db->updateData('temp_mslist_hist', ['mslist_sunday = "'. $row['SUNDAY'] .'"'],
                        ['mslist_patcode = "' . $row['methascan_patcode'] . '"']);
                } else if ($row['MONDAY'] != "-") {
                    $db->updateData('temp_mslist_hist', ['mslist_monday = "'. $row['MONDAY'] .'"'],
                        ['mslist_patcode = "' . $row['methascan_patcode'] . '"']);
                } else if ($row['TUESDAY'] != "-") {
                    $db->updateData('temp_mslist_hist', ['mslist_tuesday = "'. $row['TUESDAY'] .'"'],
                        ['mslist_patcode = "' . $row['methascan_patcode'] . '"']);
                } else if ($row['WEDNESDAY'] != "-") {
                    $db->updateData('temp_mslist_hist', ['mslist_wednesday = "'. $row['WEDNESDAY'] .'"'],
                        ['mslist_patcode = "' . $row['methascan_patcode'] . '"']);
                } else if ($row['THURSDAY'] != "-") {
                    $db->updateData('temp_mslist_hist', ['mslist_thursday = "'. $row['THURSDAY'] .'"'],
                        ['mslist_patcode = "' . $row['methascan_patcode'] . '"']);
                } else if ($row['FRIDAY'] != "-") {
                    $db->updateData('temp_mslist_hist', ['mslist_friday = "'. $row['FRIDAY'] .'"'],
                        ['mslist_patcode = "' . $row['methascan_patcode'] . '"']);
                } else if ($row['SATURDAY'] != "-") {
                    $db->updateData('temp_mslist_hist', ['mslist_saturday = "'. $row['SATURDAY'] .'"'],
                        ['mslist_patcode = "' . $row['methascan_patcode'] . '"']);
                }
            }
        }

        // Insert all records to mslist_hist for displaying purpose, at this point it will update some info
        $db->getResultBySQL('INSERT INTO mslist_hist SELECT patient_code as mslist_patcode, patient_name as mslist_patname, 
            patient_dose as mslist_dose, patient_volume as mslist_volume, 
            IFNULL(mslist_sunday, "-") as mslist_sunday, IFNULL(mslist_monday, "-") as mslist_monday, 
            IFNULL(mslist_tuesday, "-") as mslist_tuesday, IFNULL(mslist_wednesday, "-") as mslist_wednesday, 
            IFNULL(mslist_thursday, "-") as mslist_thursday, IFNULL(mslist_friday, "-") as mslist_friday, 
            IFNULL(mslist_saturday, "-") as mslist_saturday, patient_spubout as mslist_spubout, 
            patient_spubin as mslist_spubin, patient_m1mout as mslist_m1mout, patient_m1min as mslist_m1min 
            from patient_mstr LEFT JOIN temp_mslist_hist
            ON mslist_patcode = patient_code WHERE patient_kk = "' . $kk . '" ORDER BY patient_code');

        // It will check if any spubout record in database that matched with selected week range
        $SPUBPatResult = $db->getResultBySQL('SELECT * FROM spubout_mstr 
            WHERE (spubout_supplyfrom >= "' . $firstDayOfWeek . '" AND spubout_supplyfrom < "' . $lastDayOfWeek . '") 
            OR (spubout_supplyto >= "' . $firstDayOfWeek . '" AND spubout_supplyto < "' . $lastDayOfWeek . '")
            AND spubout_kk = "' . $kk . '"');

        // If there is record of spubout, it will update selected day with "spubout" to indicate user
        while ($row = $SPUBPatResult->fetch_assoc()) {
            $dateFrom = new DateTime($row['spubout_supplyfrom']);
            $dateTo = new DateTime($row['spubout_supplyto']);
            $dateTo->modify('+1 day');
            $minDate = $dateFrom->format('Y-m-d');
            $maxDate = $dateTo->format('Y-m-d');
            
            if ($minDate < $firstDayOfWeek) {
                $minDate = $firstDayOfWeek;
            }

            if ($maxDate > $lastDayOfWeek) {
                $maxDate = $lastDayOfWeek;
            }

            while ($minDate < $maxDate) {
                $row['spubout_patcode'];
                $dayNameFormat = new DateTime($minDate);
                $dayName = $dayNameFormat->format('l');

                switch ($dayName) {
                    case 'Sunday':
                        $db->getResultBySQL('UPDATE mslist_hist SET mslist_sunday = "spubout" 
                            WHERE mslist_patcode = "' . $row['spubout_patcode'] . '"');
                        break;
                    case 'Monday':
                        $db->getResultBySQL('UPDATE mslist_hist SET mslist_monday = "spubout" 
                            WHERE mslist_patcode = "' . $row['spubout_patcode'] . '"');
                        break;
                    case 'Tuesday':
                        $db->getResultBySQL('UPDATE mslist_hist SET mslist_tuesday = "spubout" 
                            WHERE mslist_patcode = "' . $row['spubout_patcode'] . '"');
                        break;
                    case 'Wednesday':
                        $db->getResultBySQL('UPDATE mslist_hist SET mslist_wednesday = "spubout" 
                            WHERE mslist_patcode = "' . $row['spubout_patcode'] . '"');
                        break;
                    case 'Thursday':
                        $db->getResultBySQL('UPDATE mslist_hist SET mslist_thursday = "spubout" 
                            WHERE mslist_patcode = "' . $row['spubout_patcode'] . '"');
                        break;
                    case 'Friday':
                        $db->getResultBySQL('UPDATE mslist_hist SET mslist_friday = "spubout" 
                            WHERE mslist_patcode = "' . $row['spubout_patcode'] . '"');
                        break;
                    case 'Saturday':
                        $db->getResultBySQL('UPDATE mslist_hist SET mslist_saturday = "spubout" 
                            WHERE mslist_patcode = "' . $row['spubout_patcode'] . '"');
                        break;
                    default:
                        break;
                }
                $addMinDate = new DateTime($minDate);
                $addMinDate->modify('+1 day');
                $minDate = $addMinDate->format('Y-m-d');
            }
        }

        // It will check if any spubout record in database that matched with selected week range
        $M1MPatResult = $db->getResultBySQL('SELECT * FROM m1mout_mstr 
            WHERE (m1mout_dateout >= "' . $firstDayOfWeek . '" AND m1mout_dateout < "' . $lastDayOfWeek . '") 
            OR (m1mout_datein >= "' . $firstDayOfWeek . '" AND m1mout_datein < "' . $lastDayOfWeek . '")
            AND m1mout_kk = "' . $kk . '"');

        // If there is record of spubout, it will update selected day with "spubout" to indicate user
        while ($row = $M1MPatResult->fetch_assoc()) {
            $dateFrom = new DateTime($row['m1mout_dateout']);
            $dateTo = new DateTime($row['m1mout_datein']);
            $dateTo->modify('+1 day');
            $minDate = $dateFrom->format('Y-m-d');
            $maxDate = $dateTo->format('Y-m-d');
            
            if ($minDate < $firstDayOfWeek) {
                $minDate = $firstDayOfWeek;
            }

            if ($maxDate > $lastDayOfWeek) {
                $maxDate = $lastDayOfWeek;
            }

            while ($minDate < $maxDate) {
                $row['m1mout_patcode'];
                $dayNameFormat = new DateTime($minDate);
                $dayName = $dayNameFormat->format('l');

                switch ($dayName) {
                    case 'Sunday':
                        $db->getResultBySQL('UPDATE mslist_hist SET mslist_sunday = "m1mout" 
                            WHERE mslist_patcode = "' . $row['m1mout_patcode'] . '"');
                        break;
                    case 'Monday':
                        $db->getResultBySQL('UPDATE mslist_hist SET mslist_monday = "m1mout" 
                            WHERE mslist_patcode = "' . $row['m1mout_patcode'] . '"');
                        break;
                    case 'Tuesday':
                        $db->getResultBySQL('UPDATE mslist_hist SET mslist_tuesday = "m1mout" 
                            WHERE mslist_patcode = "' . $row['m1mout_patcode'] . '"');
                        break;
                    case 'Wednesday':
                        $db->getResultBySQL('UPDATE mslist_hist SET mslist_wednesday = "m1mout" 
                            WHERE mslist_patcode = "' . $row['m1mout_patcode'] . '"');
                        break;
                    case 'Thursday':
                        $db->getResultBySQL('UPDATE mslist_hist SET mslist_thursday = "m1mout" 
                            WHERE mslist_patcode = "' . $row['m1mout_patcode'] . '"');
                        break;
                    case 'Friday':
                        $db->getResultBySQL('UPDATE mslist_hist SET mslist_friday = "m1mout" 
                            WHERE mslist_patcode = "' . $row['m1mout_patcode'] . '"');
                        break;
                    case 'Saturday':
                        $db->getResultBySQL('UPDATE mslist_hist SET mslist_saturday = "m1mout" 
                            WHERE mslist_patcode = "' . $row['m1mout_patcode'] . '"');
                        break;
                    default:
                        break;
                }
                $addMinDate = new DateTime($minDate);
                $addMinDate->modify('+1 day');
                $minDate = $addMinDate->format('Y-m-d');
            }
        }

        switch ($dayNameOfWeek) {
            case 'Sunday':
                $db->getResultBySQL('UPDATE mslist_hist SET mslist_sunday = "TH" WHERE mslist_sunday = "-"');
                $db->getResultBySQL('UPDATE mslist_hist SET mslist_monday = "-" WHERE mslist_monday <> "dbb"');
                $db->getResultBySQL('UPDATE mslist_hist SET mslist_tuesday = "-" WHERE mslist_tuesday <> "dbb"');
                $db->getResultBySQL('UPDATE mslist_hist SET mslist_wednesday = "-" WHERE mslist_wednesday <> "dbb"');
                $db->getResultBySQL('UPDATE mslist_hist SET mslist_thursday = "-" WHERE mslist_thursday <> "dbb"');
                $db->getResultBySQL('UPDATE mslist_hist SET mslist_friday = "-" WHERE mslist_friday <> "dbb"');
                $db->getResultBySQL('UPDATE mslist_hist SET mslist_saturday = "-" WHERE mslist_saturday <> "dbb"');
                break;
            case 'Monday':
                $db->getResultBySQL('UPDATE mslist_hist SET mslist_sunday = "TH" WHERE mslist_sunday = "-"');
                $db->getResultBySQL('UPDATE mslist_hist SET mslist_monday = "TH" WHERE mslist_monday = "-"');
                $db->getResultBySQL('UPDATE mslist_hist SET mslist_tuesday = "-" WHERE mslist_tuesday <> "dbb"');
                $db->getResultBySQL('UPDATE mslist_hist SET mslist_wednesday = "-" WHERE mslist_wednesday <> "dbb"');
                $db->getResultBySQL('UPDATE mslist_hist SET mslist_thursday = "-" WHERE mslist_thursday <> "dbb"');
                $db->getResultBySQL('UPDATE mslist_hist SET mslist_friday = "-" WHERE mslist_friday <> "dbb"');
                $db->getResultBySQL('UPDATE mslist_hist SET mslist_saturday = "-" WHERE mslist_saturday <> "dbb"');
                break;
            case 'Tuesday':
                $db->getResultBySQL('UPDATE mslist_hist SET mslist_sunday = "TH" WHERE mslist_sunday = "-"');
                $db->getResultBySQL('UPDATE mslist_hist SET mslist_monday = "TH" WHERE mslist_monday = "-"');
                $db->getResultBySQL('UPDATE mslist_hist SET mslist_tuesday = "TH" WHERE mslist_tuesday = "-"');
                $db->getResultBySQL('UPDATE mslist_hist SET mslist_wednesday = "-" WHERE mslist_wednesday <> "dbb"');
                $db->getResultBySQL('UPDATE mslist_hist SET mslist_thursday = "-" WHERE mslist_thursday <> "dbb"');
                $db->getResultBySQL('UPDATE mslist_hist SET mslist_friday = "-" WHERE mslist_friday <> "dbb"');
                $db->getResultBySQL('UPDATE mslist_hist SET mslist_saturday = "-" WHERE mslist_saturday <> "dbb"');
                break;
            case 'Wednesday':
                $db->getResultBySQL('UPDATE mslist_hist SET mslist_sunday = "TH" WHERE mslist_sunday = "-"');
                $db->getResultBySQL('UPDATE mslist_hist SET mslist_monday = "TH" WHERE mslist_monday = "-"');
                $db->getResultBySQL('UPDATE mslist_hist SET mslist_tuesday = "TH" WHERE mslist_tuesday = "-"');
                $db->getResultBySQL('UPDATE mslist_hist SET mslist_wednesday = "TH" WHERE mslist_wednesday = "-"');
                $db->getResultBySQL('UPDATE mslist_hist SET mslist_thursday = "-" WHERE mslist_thursday <> "dbb"');
                $db->getResultBySQL('UPDATE mslist_hist SET mslist_friday = "-" WHERE mslist_friday <> "dbb"');
                $db->getResultBySQL('UPDATE mslist_hist SET mslist_saturday = "-" WHERE mslist_saturday <> "dbb"');
                break;
            case 'Thursday':
                $db->getResultBySQL('UPDATE mslist_hist SET mslist_sunday = "TH" WHERE mslist_sunday = "-"');
                $db->getResultBySQL('UPDATE mslist_hist SET mslist_monday = "TH" WHERE mslist_monday = "-"');
                $db->getResultBySQL('UPDATE mslist_hist SET mslist_tuesday = "TH" WHERE mslist_tuesday = "-"');
                $db->getResultBySQL('UPDATE mslist_hist SET mslist_wednesday = "TH" WHERE mslist_wednesday = "-"');
                $db->getResultBySQL('UPDATE mslist_hist SET mslist_thursday = "TH" WHERE mslist_thursday = "-"');
                $db->getResultBySQL('UPDATE mslist_hist SET mslist_friday = "-" WHERE mslist_friday <> "dbb"');
                $db->getResultBySQL('UPDATE mslist_hist SET mslist_saturday = "-" WHERE mslist_saturday <> "dbb"');
                break;
            case 'Friday':
                $db->getResultBySQL('UPDATE mslist_hist SET mslist_sunday = "TH" WHERE mslist_sunday = "-"');
                $db->getResultBySQL('UPDATE mslist_hist SET mslist_monday = "TH" WHERE mslist_monday = "-"');
                $db->getResultBySQL('UPDATE mslist_hist SET mslist_tuesday = "TH" WHERE mslist_tuesday = "-"');
                $db->getResultBySQL('UPDATE mslist_hist SET mslist_wednesday = "TH" WHERE mslist_wednesday = "-"');
                $db->getResultBySQL('UPDATE mslist_hist SET mslist_thursday = "TH" WHERE mslist_thursday = "-"');
                $db->getResultBySQL('UPDATE mslist_hist SET mslist_friday = "TH" WHERE mslist_friday = "-"');
                $db->getResultBySQL('UPDATE mslist_hist SET mslist_saturday = "-" WHERE mslist_saturday <> "dbb"');
                break;
            case 'Saturday':
                $db->getResultBySQL('UPDATE mslist_hist SET mslist_sunday = "TH" WHERE mslist_sunday = "-"');
                $db->getResultBySQL('UPDATE mslist_hist SET mslist_monday = "TH" WHERE mslist_monday = "-"');
                $db->getResultBySQL('UPDATE mslist_hist SET mslist_tuesday = "TH" WHERE mslist_tuesday = "-"');
                $db->getResultBySQL('UPDATE mslist_hist SET mslist_wednesday = "TH" WHERE mslist_wednesday = "-"');
                $db->getResultBySQL('UPDATE mslist_hist SET mslist_thursday = "TH" WHERE mslist_thursday = "-"');
                $db->getResultBySQL('UPDATE mslist_hist SET mslist_friday = "TH" WHERE mslist_friday = "-"');
                $db->getResultBySQL('UPDATE mslist_hist SET mslist_saturday = "TH" WHERE mslist_saturday = "-"');
                break;
            default:
                break;
        }

        // Get final result for display complete master list
        $scanResult = $db->getResultBySQL('SELECT * from mslist_hist');
    }

?>

<!DOCTYPE html>
<html>
<head>
    <title>MethaSys-Master List Report</title>
    <link rel="stylesheet" type="text/css" href="../css/masterStyle.css">
    <style type="text/css" media="print">
        @page {size: landscape;}
    </style>
</head>
<body>
<div id="header">
    <?php require_once '../includes/titleHeader.php';?>
        <label>MethaSys - Master List Page</label>
    <?php require_once '../includes/titleSubFooter.php';?>
    <?php require_once '../includes/menuHeader.php';?>
            <li><a href="../announcement.php">Annoucement</a></li>
            <li><a href="../scan.php">Scan</a></li>
            <li><a href="../home.php">Log</a></li>
            <li><a href="../spubm1m.php">SPUB/M1M</a></li>
            <li><a href="../maintenance.php">Maintenance</a></li>
            <li><a href="../report.php" style="color: #FFFFFF;font-size: 18px;text-decoration: none;">Report</a></li>
    <?php require_once '../includes/menuFooter.php';?>
</div>
    <div id="content">
        <form method="post">
            <div style="text-align:center;padding-bottom:20px;font-weight:bold;">
                <label>Date: </label>
                <input type="date" id="date-selector" name="date-selector" value="<?php echo $fromDate;?>" oninput="this.form.submit()">  
            </div>
            <div id="patient-log">
                <table width="100%" id="patient-table">
                    <tr>
                        <th>No</th>
                        <th>Code</th>
                        <th>Name</th>
                        <th>Dose(mg)</th>
                        <th>Volume(ml)</th>
                        <th>Sunday</th>
                        <th>Monday</th>
                        <th>Tuesday</th>
                        <th>Wednesday</th>
                        <th>Thursday</th>
                        <th>Friday</th>
                        <th>Saturday</th>
                    </tr>
                    <?php 
                        $count = 1;
                        $totalDose = 0;
                        $totalVolume = 0;
                        while ($row = $scanResult->fetch_assoc()){
                            if ($count%2 == 0) {
                                echo '<tr class="even">';
                            } else {
                                echo '<tr>';
                            }                       
                            echo '<td>' . $count . '</td>';
                            echo '<td>' . $row['mslist_patcode'] . '</td>';
                            echo '<td>' . $row['mslist_patname'] . '</td>';
                            echo '<td>' . $row['mslist_dose'] . '</td>';
                            echo '<td>' . $row['mslist_volume'] . '</td>';
                            echo '<td style="text-align:center;">' . $row['mslist_sunday'] . '</td>';
                            echo '<td style="text-align:center;">' . $row['mslist_monday'] . '</td>';
                            echo '<td style="text-align:center;">' . $row['mslist_tuesday'] . '</td>';
                            echo '<td style="text-align:center;">' . $row['mslist_wednesday'] . '</td>';
                            echo '<td style="text-align:center;">' . $row['mslist_thursday'] . '</td>';
                            echo '<td style="text-align:center;">' . $row['mslist_friday'] . '</td>';
                            echo '<td style="text-align:center;">' . $row['mslist_saturday'] . '</td>';
                            echo '</t style="text-align:center;"r>';       
                            
                            $count++;
                            $totalDose += intval($row['mslist_dose']);
                            $totalVolume += intval($row['mslist_volume']);
                        }
                    ?>
                </table>
            </div>  
        </form>
    </div>     
    <?php require_once '../includes/pageSubFooter.php';?>
</body>
</html>
