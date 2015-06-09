<?php
    require_once 'includes/session.php';
    require_once 'classes/Validator.php';
    require_once 'classes/MySQLConnector.php';
    require_once 'classes/Date.php';

    $db = new MySQLConnector('localhost', 'leoboey_db', 'methasys2015', 'leoboey_db');
    date_default_timezone_set('Asia/Kuala_Lumpur');
    $date = date("Y-m-d H:i:s");

    $timeZone = new DateTimeZone('Asia/Kuala_Lumpur');
    $newDate = new Date($timeZone);
    $newDate->addDays(1);
    $pureDate = $newDate->getMySQLFormat();
    $kk = $_COOKIE['kk'];
?>

<!DOCTYPE html>
<html>
<head>
    <title>MethaSys-Scan</title>
    <link rel="stylesheet" type="text/css" href="css/mystyle.css">
    <link rel="stylesheet" href="scripts/jquery-ui-1.11.2/jquery-ui.min.css">
    <script src="scripts/jquery-1.11.2.min.js"></script>
    <script src="scripts/jquery-ui-1.11.2/jquery-ui.min.js"></script>
    <script>

        $("document").ready(function(){

            $.post("scripts/checkFirstTrans.php",function(data){
                if(data.first_trans) {
                    $("#dialog-opening-stock").dialog({
                        resizable: false,
                        modal: true,
                        buttons: {
                            "Proceed": function() {
                                var userinput = $("#opening").val();
                                if (userinput <= 0) {
                                } else {
                                    $.post("scripts/setOpeningStock.php",{opening:userinput},function(data){
                                        if (data.insert == true) {
                                            alert("Stock Out created! " + data.opening + "ml of methadone is taken out from inventory.");
                                        }
                                    },"json");
                                    $( this ).dialog( "close" );    
                                }
                            }
                        }
                    });
                } else {
                    $("#dialog-opening-stock").remove();
                }

            },"json");

            $.post("scripts/checkBalExist.php",function(data){
                if(data.first_trans) {
                    $("#dialog-balance-stock").dialog({
                        resizable: false,
                        modal: true,
                        buttons: {
                            "Proceed": function() {
                                var userinput = $("#balance").val();
                                if (userinput <= 0) {
                                    
                                } else {
                                    $.post("scripts/setLastBal.php",{opening:userinput},function(data){
                                        if (data.insert == true) {
                                            // alert("The latest balance volume of methadone in bottle is " + data.opening + "ml.");
                                            
                                        }
                                    },"json");
                                    $( this ).dialog( "close" );    
                                }

                            }
                        },
                    });
                } else {
                    $("#dialog-balance-stock").remove();
                }

            },"json");

        });


        $("document").ready(function(){
            $("#dbb").click(function() {
                $("#dialog-dbb").css("visibility", "visible");

                $("#dialog-dbb").dialog({
                    width: 750,
                    resizable: false,
                    modal: true,
                    buttons: {
                        "Proceed" : function() {
                            var fromDate = $("#date-from").val();
                            var toDate = $("#date-to").val();
                            var patientCode = $("#patient-code").val();
                            var patientName = $("#patient-name").val();
                            var patientDose = $("#patient-dose").val();
                            var patientVolume = $("#patient-volume").val();
                            var patientMethatype = $("#patient-methatype").val();
                            var patientStatus = $("#patient-status").val();
                            // var kk = $("#kk").text();
                            patJson = {
                                from: fromDate,
                                to: toDate,
                                patcode: patientCode,
                                patname: patientName,
                                dose: patientDose,
                                volume: patientVolume,
                                methatype: patientMethatype,
                                patstatus: patientStatus
                            };

                            $.post("scripts/setDBB.php",patJson,function(data){
                                var inserted = data.insert;
                                var dbbExisted = data.dbbExist;
                                var dotExisted = data.dotExist;
                                if (data.insert == true) {
                                    alert("DBB INSERTED! From " + data.fromDate + " to " + data.toDate);
                                    window.location.reload();
                                } else {
                                    if (data.dbbExist) {
                                        alert("DBB NOT INSERT! DBB existed for selected date range!");
                                        window.location.reload();
                                    } else if (data.dotExist) {
                                        alert("DBB NOT INSERT! DOT existed for selected date range!");
                                        window.location.reload();
                                    }
                                }
                            },"json");
                            $( this ).dialog( "close" ); 
                        },
                        "Cancel" : function() {
                            $( this ).dialog( "close" ); 
                        }
                    }
                });
            });
        });

        $("document").ready(function(){
            $("#dot").click(function() {
                var dbbDisabled = $("#dbb").prop("disabled");

                if (dbbDisabled) {
                    var txt;
                    var r = confirm("Are you sure want to proceed for DOT?");
                    if (r == true) {
                        txt = "You pressed OK!";
                    } else {
                        txt = "You pressed Cancel!";
                    }
                    document.getElementById("demo").innerHTML = txt;
                } else {
                    var patientCode = $("#patient-code").val();
                    var patientName = $("#patient-name").val();
                    var patientDose = $("#patient-dose").val();
                    var patientVolume = $("#patient-volume").val();
                    var patientMethatype = $("#patient-methatype").val();
                    var patientStatus = $("#patient-status").val();
                    // var kk = $("#kk").text();
                    
                    patJson = {
                        patcode: patientCode,
                        patname: patientName,
                        dose: patientDose,
                        volume: patientVolume,
                        methatype: patientMethatype,
                        patstatus: patientStatus
                    };

                    $.post("scripts/setDOT.php",patJson,function(data){
                        if (data.insert == true) {
                            // alert("Record inserted!");
                            window.location.reload();
                        }          
                    });
                }
            });
        });

    </script>
    <script type="text/javascript">

        function toggleTimer() {
            var codeTimer;
            var scanbutton = document.getElementById("scanButton");
            var typebutton = document.getElementById("typeButton");
            var rcodeObj = document.getElementById("rcode");
            var insertModeObj = document.getElementById("insertMode");

            if (!codeTimer) {
                codeTimer = setInterval(function() {refreshTimer()}, 500);
            }

            function setTimer() {
                codeTimer = setInterval(function() {refreshTimer()}, 500);
                scanbutton.style.backgroundColor = '#85ACD3';
                typebutton.style.backgroundColor = '#FFFFFF';
                scanbutton.style.color = '#FFFFFF';
                typebutton.style.color = '#0052A3';
                rcodeObj.value = '';
                rcodeObj.focus();
            }

            function clearTimer() {
                clearInterval(codeTimer);
                scanbutton.style.backgroundColor = '#FFFFFF';
                typebutton.style.backgroundColor = '#85ACD3';
                scanbutton.style.color = '#0052A3';
                typebutton.style.color = '#FFFFFF';
                rcodeObj.value = '';
                rcodeObj.focus();
            }

            function refreshTimer() {
                rcodeObj.value = '';
                rcodeObj.focus();
            }

            scanbutton.onclick = setTimer;
            typebutton.onclick = clearTimer;
        }

        function getPatientInfo() {

            document.getElementById("rcode").style.textTransform = "uppercase";

            if (window.XMLHttpRequest) {
                ajaxRequest = new XMLHttpRequest();
            } else {
                ajaxRequest = new ActiveXObject("Microsoft.XMLHTTP");
            }

            ajaxRequest.onreadystatechange = function() {
                if (ajaxRequest.readyState == 4 && ajaxRequest.status == 200) {
                    var jsonObj = JSON.parse(ajaxRequest.responseText);

                    var dbbattended = jsonObj.patient_dbbattended;
                    var dotattended = jsonObj.patient_dotattended;
                    var suspended = jsonObj.patient_suspended;
                    var lost = jsonObj.patient_lost;
                    var terminated = jsonObj.patient_terminated;
                    var death = jsonObj.patient_death;
                    var transferOut = jsonObj.patient_transout;
                    var spubOut = jsonObj.patient_spubout;
                    var spubIn = jsonObj.patient_spubin;
                    var m1mOut = jsonObj.patient_m1mout;
                    var m1mIn = jsonObj.patient_m1min;

                    document.getElementById("patient-date").value = jsonObj.patient_date;
                    document.getElementById("patient-absenceDay").value = jsonObj.patient_absenceDay;
                    document.getElementById("patient-code").value = jsonObj.patient_code;
                    document.getElementById("patient-name").value = jsonObj.patient_name;
                    document.getElementById("patient-methatype").value = jsonObj.patient_methatype;
                    document.getElementById("patient-dose").value = jsonObj.patient_dose;
                    document.getElementById("patient-volume").value = jsonObj.patient_volume;
                    document.getElementById("patient-age").value = jsonObj.patient_age;
                    document.getElementById("patient-gender").value = jsonObj.patient_gender;
                    document.getElementById("patient-status").value = jsonObj.patient_status;
                    document.getElementById("patient-ename").value = jsonObj.patient_ename;
                    document.getElementById("patient-econtact").value = jsonObj.patient_econtact;
                    document.getElementById("patient-photo").src = 'photos/' + jsonObj.patient_code + '.jpg';

                    if (dbbattended && !dotattended) {
                        document.getElementById("terminateLabel").innerHTML = 'PATIENT DBB'; 
                        document.getElementById("dot").disabled = true;
                        document.getElementById("terminateLabel").style.color = "#005C00";
                        document.getElementById("dot").style.backgroundColor = "#B80000";
                        document.getElementById("dbb").disabled = false;
                        document.getElementById("dbb").style.backgroundColor = "#005C00";
                    } else if (!dbbattended && dotattended) {
                        document.getElementById("terminateLabel").innerHTML = 'PATIENT DOT'; 
                        document.getElementById("dot").disabled = true;
                        document.getElementById("terminateLabel").style.color = "#005C00";
                        document.getElementById("dot").style.backgroundColor = "#B80000";
                        document.getElementById("dbb").disabled = false;
                        document.getElementById("dbb").style.backgroundColor = "#005C00";
                    } else if (suspended) {
                        document.getElementById("terminateLabel").innerHTML = 'SUSPENDED!'; 
                        document.getElementById("dot").disabled = true;
                        document.getElementById("terminateLabel").style.color = "#B80000";
                        document.getElementById("dot").style.backgroundColor = "#B80000";
                        document.getElementById("dbb").disabled = true;
                        document.getElementById("dbb").style.backgroundColor = "#B80000";
                    } else if (lost) {
                        document.getElementById("terminateLabel").innerHTML = 'LOST!'; 
                        document.getElementById("dot").disabled = true;
                        document.getElementById("terminateLabel").style.color = "#B80000";
                        document.getElementById("dot").style.backgroundColor = "#B80000";
                        document.getElementById("dbb").disabled = true;
                        document.getElementById("dbb").style.backgroundColor = "#B80000";
                    } else if (terminated) {
                        document.getElementById("terminateLabel").innerHTML = 'TERMINATED!'; 
                        document.getElementById("dot").disabled = true;
                        document.getElementById("terminateLabel").style.color = "#B80000";
                        document.getElementById("dot").style.backgroundColor = "#B80000";
                        document.getElementById("dbb").disabled = true;
                        document.getElementById("dbb").style.backgroundColor = "#B80000";
                    } else if (death) {
                        document.getElementById("terminateLabel").innerHTML = 'NEW WORLD!(CHECK STATUS)'; 
                        document.getElementById("dot").disabled = true;
                        document.getElementById("terminateLabel").style.color = "#B80000";
                        document.getElementById("dot").style.backgroundColor = "#B80000";
                        document.getElementById("dbb").disabled = true;
                        document.getElementById("dbb").style.backgroundColor = "#B80000";
                    } else if (transferOut) {
                        document.getElementById("terminateLabel").innerHTML = 'TRANSFER OUT!'; 
                        document.getElementById("dot").disabled = true;
                        document.getElementById("terminateLabel").style.color = "#B80000";
                        document.getElementById("dot").style.backgroundColor = "#B80000";
                        document.getElementById("dbb").disabled = true;
                        document.getElementById("dbb").style.backgroundColor = "#B80000";
                    } else if (spubOut) {
                        document.getElementById("terminateLabel").innerHTML = 'SPUB OUT!'; 
                        document.getElementById("dot").disabled = true;
                        document.getElementById("terminateLabel").style.color = "#B80000";
                        document.getElementById("dot").style.backgroundColor = "#B80000";
                        document.getElementById("dbb").disabled = true;
                        document.getElementById("dbb").style.backgroundColor = "#B80000";
                    } else if (spubIn) {
                        document.getElementById("terminateLabel").innerHTML = 'SPUB IN'; 
                        document.getElementById("dot").disabled = false;
                        document.getElementById("terminateLabel").style.color = "#005C00";
                        document.getElementById("dot").style.backgroundColor = "#005C00";
                        document.getElementById("dbb").disabled = false;
                        document.getElementById("dbb").style.backgroundColor = "#005C00";
                    } else if (m1mOut) {
                        document.getElementById("terminateLabel").innerHTML = 'M1M OUT!'; 
                        document.getElementById("dot").disabled = true;
                        document.getElementById("terminateLabel").style.color = "#B80000";
                        document.getElementById("dot").style.backgroundColor = "#B80000";
                        document.getElementById("dbb").disabled = true;
                        document.getElementById("dbb").style.backgroundColor = "#B80000";
                    } else if (m1mIn) {
                        document.getElementById("terminateLabel").innerHTML = 'M1M IN'; 
                        document.getElementById("dot").disabled = false;
                        document.getElementById("terminateLabel").style.color = "#B80000";
                        document.getElementById("dot").style.backgroundColor = "#B80000";
                        document.getElementById("dbb").disabled = false;
                        document.getElementById("dbb").style.backgroundColor = "#B80000";
                    } else {                       
                        document.getElementById("terminateLabel").innerHTML = 'Please Scan/Type Bar Code';
                        if (document.getElementById("patient-name").value.trim() != '') {
                            document.getElementById("dot").disabled = false;
                            document.getElementById("terminateLabel").innerHTML = 'NORMAL';
                            document.getElementById("dot").style.backgroundColor = "#005C00";
                            document.getElementById("terminateLabel").style.color = "#005C00";
                            document.getElementById("dbb").disabled = false;
                            document.getElementById("dbb").style.backgroundColor = "#005C00";
                        } else {
                            document.getElementById("dot").disabled = true;
                            document.getElementById("dot").style.backgroundColor = "#B80000";
                            document.getElementById("terminateLabel").style.color = "#B80000";
                            document.getElementById("dbb").disabled = true;
                            document.getElementById("dbb").style.backgroundColor = "#B80000";
                        }
                    }
                }
            }

            var code = document.getElementById("rcode").value;
            var kk = document.getElementById("kk").value;

            ajaxRequest.open("POST", "scripts/patient_info_ajax.php?code="+code+"&kk="+kk, true);
            ajaxRequest.send();
        }

        function alertMsg() {
            var patientCode = document.getElementById("rcode").value;
            // if (confirm("Are you sure to proceed DBB(DOS BAWA BALIK) for patient, " + patientCode.toUpperCase() + "?"))
            //     document.forms[0].submit();
            // else
            //     return false;
            var period = prompt("Please enter days number for DBB(start from tomorrow date)", "1");
            if (period != null) {
                document.getElementById("period").value = period;
                document.forms[0].submit();
            } else {
                return false;
            }
        }
    </script>
</head>
<body>
<div id="header">
    <?php require_once 'includes/titleHeader.php';?>
        <label>MethaSys - Scan Page</label>
    <?php require_once 'includes/titleFooter.php';?>
    <?php require_once 'includes/menuHeader.php';?>
            <li><a href="announcement.php">Annoucement</a></li>
            <li><a href="scan.php" style="color: #FFFFFF;font-size: 18px;text-decoration: none;">Scan</a></li>
            <li><a href="home.php">Log</a></li>
            <li><a href="spubm1m.php">SPUB/M1M</a></li>
            <li><a href="maintenance.php">Maintenance</a></li>
            <li><a href="report.php">Report</a></li>
    <?php require_once 'includes/menuFooter.php';?>
</div>
    <div id="content">
            <div id="content-left">
                <div id='patient-info'>
                    <input type="text" id="patient-absenceDay" name="patient-absenceDay" readonly="readonly" style="color:blue">
                    <table width="100%">
                        <tr>
                            <th>Date:</th>
                            <td>
                                <input type="text" id="patient-date" name="patient-date" readonly="readonly">
                            </td>    
                        </tr>
                        <tr>
                            <th>Code:</th>
                            <td>
                                <input type="text" id="patient-code" name="patient-code" readonly="readonly">
                            </td>    
                        </tr>
                        <tr>
                            <th>Name:</th>
                            <td>
                                <input type="text" id="patient-name" name="patient-name" readonly="readonly">
                            </td>    
                        </tr>
                        <tr>
                            <th>Methadone:</th>
                            <td>
                                <input type="text" id="patient-methatype" name="patient-methatype" readonly="readonly">
                            </td>    
                        </tr>
                        <tr>
                            <th>Dose(mg):</th>
                            <td>
                                <input type="text" id="patient-dose" name="patient-dose" readonly="readonly">
                            </td>    
                        </tr>
                        <tr>
                            <th>Volume(ml):</th>
                            <td>
                                <input type="text" id="patient-volume" name="patient-volume" readonly="readonly">
                            </td>    
                        </tr>
                        <tr>
                            <th>Status:</th>
                            <td>
                                <input type="text" id="patient-status" name="patient-status" readonly="readonly">
                            </td>    
                        </tr>
                        <tr>
                            <th>Gender:</th>
                            <td>
                                <input type="text" id="patient-gender" name="patient-gender" readonly="readonly">
                            </td>    
                        </tr>
                        <tr>
                            <th>Age:</th>
                            <td>
                                <input type="text" id="patient-age" name="patient-age" readonly="readonly">
                            </td>    
                        </tr>
                        <tr>
                            <th>Emg Contact Person:</th>
                            <td>
                                <input type="text" id="patient-ename" name="patient-ename" readonly="readonly">
                            </td>    
                        </tr>
                        <tr>
                            <th>Emg Contact Number:</th>
                            <td>
                                <input type="text" id="patient-econtact" name="patient-econtact" readonly="readonly">
                            </td>    
                        </tr>
                    </table>
                </div>

            </div>

            <div id="content-right">
                <div id="content-insert-mode">
                    <ul id="modeButton">
                        <li><button type="button" id="scanButton">Scan</button></li>
                        <li><button type="button" id="typeButton">Type</button></li>                    
                    </ul>                         
                    <label style="font-size:16px;">Patient Code:</label>
                    <input type="text" id="rcode" name="rcode" autocomplete="off" oninput="getPatientInfo()" style="width:60px;">
                    <p id="kk" style="visibility:hidden;font-size:5px;height:5px;"><?echo $kk?></p>
                </div>
                <div id="content-image">
                    <label for="photo" id="patphoto" name="patphoto" style="font-size:16px;">Patient's Photo</label>
                    <br>
                    <img id="patient-photo" src="" width="200" height="240">
                    <br>
                    <label id="terminateLabel">Please Scan/Type Bar Code</label>
                    <div id="content-info">
                        <ul id="confirmation">
                            <li><input type="submit" id="dot" name="dot" value="DOT" disabled></li>
                            <li><button id="dbb" name="dbb" value="DBB" disabled>DBB</button></li>
                        </ul>                
                    </div>                
                </div>
            </div>
 
    </div>     
    <?php require_once 'includes/pageFooter.php';?>
    <div id="dialog-balance-stock" title="Balance Volume In Bottle" style="text-align:center;">
        <p>Please enter latest reading balance volume of methadone(ml) in bottle</p> 
        <input type="text" id="balance" value="0" style="width:100px;">
    </div>
    <div id="dialog-opening-stock" title="Stock Out" style="text-align:center;">
        <p>Please enter stock out volume of methadone(ml)</p> 
        <input type="text" id="opening" value="0" style="width:100px;">
    </div>
    <div id="dialog-dbb" title="DBB" style="text-align:center; visibility:hidden">
        <p>Please select period for DBB</p>
        From Date : <input type="date" id="date-from" style="width:200px" value="<?php echo $pureDate;?>">
        To Date : <input type="date" id="date-to" style="width:200px" value="<?php echo $pureDate;?>">
    </div>

    <script type="text/javascript">
        toggleTimer();
    </script>
</body>
</html>
