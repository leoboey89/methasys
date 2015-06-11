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
    $user = $_COOKIE['user'];
    $kk = $_COOKIE['kk'];

    $isKkHQ = $db->isExist('kk_mstr', ['kk_code = "' . $kk . '"', 'kk_hq = "Y"']);

    if ($isKkHQ) {
        $spubPermResult = $db->getResultSet('spubperm_mstr', ['spubperm_code', 'spubperm_patcode'], ['spubperm_active = "Y"'], ['spubperm_id'], ['spubperm_code']);    
    }
    
?>

<!DOCTYPE html>
<html>
<head>
    <title>MethaSys-SPUB Permanent</title>
    <link rel="stylesheet" type="text/css" href="../css/spubPermStyle.css">
    <script src="../scripts/jquery-1.11.2.min.js"></script>
    <script>
        $("document").ready(function(){
            $( "#spubperm-code" ).change(function() {
                var spubpermCode = $("#spubperm-code").val();

                if (spubpermCode == 'Create New') {
                    $.post("../scripts/getKkInfo.php", function(data){
                        $("#spubperm-kk").empty();
                        $.each(data, function(code, name) {
                            $("#spubperm-kk").append($('<option>', {
                                value: code,
                                text: name
                            }));
                        });                       
                    }, "json");

                    $("#spubperm-form").css("visibility", "visible");
                    $("#spubperm-patcode").empty();
                    $("#spubperm-active").val("Y");
                    $("#insert").text("Create");
                    $("#spubperm-sunday").prop("checked", false);
                    $("#spubperm-monday").prop("checked", false);
                    $("#spubperm-tuesday").prop("checked", false);
                    $("#spubperm-wednesday").prop("checked", false);
                    $("#spubperm-thursday").prop("checked", false);
                    $("#spubperm-friday").prop("checked", false);
                    $("#spubperm-saturday").prop("checked", false);
                } else if (spubpermCode == '') {
                    $("#spubperm-form").css("visibility", "hidden");
                    $("#spubperm-kk").val("");
                    $("#spubperm-patcode").val("");
                    $("#spubperm-active").val("");
                    $("#spubperm-sunday").prop("checked", false);
                    $("#spubperm-monday").prop("checked", false);
                    $("#spubperm-tuesday").prop("checked", false);
                    $("#spubperm-wednesday").prop("checked", false);
                    $("#spubperm-thursday").prop("checked", false);
                    $("#spubperm-friday").prop("checked", false);
                    $("#spubperm-saturday").prop("checked", false);
                } else {
                    $("#spubperm-form").css("visibility", "visible");

                    $("#insert").text("Update");

                    var permCode = $("#spubperm-code").val();
                    
                    patInfoJson = {
                        spubPermCode: permCode
                    };

                    $.post("../scripts/getSpubPermPatInfo.php", patInfoJson, function(data){
                        $("#spubperm-patcode").empty();
                        $("#spubperm-patcode").append($('<option>', {
                            value: data.patcode,
                            text: data.patcode
                        }));
                        $("#spubperm-kk").empty();
                        $("#spubperm-kk").append($('<option>', {
                            value: data.kk,
                            text: data.kk
                        }));
                        $("#spubperm-active").filter(function() {
                            return $(this).text() == data.active;
                        }).prop('selected', true);
                        
                        $("#spubperm-from").val(data.from);
                        $("#spubperm-to").val(data.to);
                        
                        if (data.sunday == "Y") {
                            $("#spubperm-sunday").prop("checked", true);
                        } else {
                            $("#spubperm-sunday").prop("checked", false);
                        }

                        if (data.monday == "Y") {
                            $("#spubperm-monday").prop("checked", true);
                        } else {
                            $("#spubperm-monday").prop("checked", false);
                        }

                        if (data.tuesday == "Y") {
                            $("#spubperm-tuesday").prop("checked", true);
                        } else {
                            $("#spubperm-tuesday").prop("checked", false);
                        }

                        if (data.wednesday == "Y") {
                            $("#spubperm-wednesday").prop("checked", true);
                        } else {
                            $("#spubperm-wednesday").prop("checked", false);
                        }

                        if (data.thursday == "Y") {
                            $("#spubperm-thursday").prop("checked", true);
                        } else {
                            $("#spubperm-thursday").prop("checked", false);
                        }

                        if (data.friday == "Y") {
                            $("#spubperm-friday").prop("checked", true);
                        } else {
                            $("#spubperm-friday").prop("checked", false);
                        }

                        if (data.saturday == "Y") {
                            $("#spubperm-saturday").prop("checked", true);
                        } else {
                            $("#spubperm-saturday").prop("checked", false);
                        }
                    }, "json");
                }
            });

            $( "#spubperm-kk" ).change(function() {
                var kkCode = $("#spubperm-kk").val();

                $.post("../scripts/getKkPatInfo.php", {kkcode: kkCode}, function(data){
                    $("#spubperm-patcode").empty();
                    for (var i = 0; i < data.length ; i++) {
                        $("#spubperm-patcode").append($('<option>', {
                            value: data[i],
                            text: data[i]
                        }));
                    }

                }, "json");

            });

            $("#insert").click(function(){
                var status = $("#insert").text();
                var sunday = $("#spubperm-sunday").prop('checked');
                var monday = $("#spubperm-monday").prop('checked');
                var tuesday = $("#spubperm-tuesday").prop('checked');
                var wednesday = $("#spubperm-wednesday").prop('checked');
                var thursday = $("#spubperm-thursday").prop('checked');
                var friday = $("#spubperm-friday").prop('checked');
                var saturday = $("#spubperm-saturday").prop('checked');
                var patcode = $("#spubperm-patcode").val();
                var fromkk = $("#spubperm-kk").val();
                var active = $("#spubperm-active").val();
                var code = $("#spubperm-code").val();
                var from = $("#spubperm-from").val();
                var to = $("#spubperm-to").val();
                var count = 0;
                var contCount = 0;

                if (sunday) {
                    contCount++;
                } 

                if (monday) {
                    if (!sunday) {
                        contCount++;
                    }
                } 

                if (tuesday) {
                    if (!monday) {
                        contCount++;
                    }
                } 

                if (wednesday) {
                    if (!tuesday) {
                        contCount++;
                    }
                } 

                if (thursday) {
                    if (!wednesday) {
                        contCount++;
                    }
                } 

                if (friday) {
                    if (!thursday) {
                        contCount++;
                    }
                } 

                if (saturday) {
                    if (!friday) {
                        contCount++;
                    }
                } 

                if (sunday) {
                    if (!saturday) {
                        contCount++;
                    }
                    contCount--;
                } 

                if (sunday) {
                    sunday = "Y";
                } else {
                    sunday = "N";
                }

                if (monday) {
                    monday = "Y";
                } else {
                    monday = "N";
                }

                if (tuesday) {
                    tuesday = "Y";
                } else {
                    tuesday = "N";
                }

                if (wednesday) {
                    wednesday = "Y";
                } else {
                    wednesday = "N";
                }

                if (thursday) {
                    thursday = "Y";
                } else {
                    thursday = "N";
                }

                if (friday) {
                    friday = "Y";
                } else {
                    friday = "N";
                }

                if (saturday) {
                    saturday = "Y";
                } else {
                    saturday = "N";
                }

                if (!patcode) {
                    alert("Please select patient code!");
                } else if ((sunday == 'N') && (monday == 'N') && (tuesday == 'N') && (wednesday == 'N') 
                    && (thursday == 'N') && (friday == 'N') && (saturday == 'N')) {
                    alert("Please day/days to attend SPUB Permanent!");
                } 
                // else if (contCount > 1) {
                //     alert("Please select days continuously!");
                // } 
                else {
                    if (status == 'Create') {
                        spubPermJson = {
                            purpose: status,
                            fromkk: fromkk,
                            patcode: patcode,
                            active: active,
                            from: from,
                            to: to,
                            sunday: sunday,
                            monday: monday,
                            tuesday: tuesday,
                            wednesday: wednesday,
                            thursday: thursday,
                            friday: friday,
                            saturday: saturday
                        }

                        $.post("../scripts/createSpubPerm.php", spubPermJson, function(data){
                            if (data.insert) {
                                alert("Successful create '" + data.code + "', SPUB Permanent for patient '" + data.patcode + "'!");
                                window.location.reload();
                            }

                        }, "json");
                    } else {
                        spubPermJson = {
                            purpose: status,
                            code: code,
                            active: active,
                            from: from,
                            to: to,
                            sunday: sunday,
                            monday: monday,
                            tuesday: tuesday,
                            wednesday: wednesday,
                            thursday: thursday,
                            friday: friday,
                            saturday: saturday
                        }

                        $.post("../scripts/createSpubPerm.php", spubPermJson, function(data){
                            if (data.update) {
                                alert("Successful update '" + data.code + "'!");
                                window.location.reload();
                            }
                        }, "json");
                    }
                }
            });

        });
    </script>
</head>
<body>
<div id="header">
    <?php require_once '../includes/titleHeader.php';?>
        <label>MethaSys - SPUB Permanent Page</label>
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
            <div id="spubForm">
                <p style="font-weight:bold;padding-left:20px;">
                    <label>SPUB Permanent</label>
                    <select id="spubperm-code" name="spubperm-code">
                        <option></option>
                        <option>Create New</option>
                        <?php
                            while ($row = $spubPermResult->fetch_assoc()) {
                                echo '<option value="' . $row['spubperm_code'] . '">' . $row['spubperm_code'] . ' >>> ' . $row['spubperm_patcode'] . '</option>';
                            }
                        ?>
                    </select>
                </p> 
                <div id="spubperm-form" style="visibility:hidden">
                    <p style="font-weight:bold;padding-left:20px;">
                        <label>Klinik Kesihatan </label>
                        <select id="spubperm-kk" name="spubperm-kk">
                        </select>
                    </p>          
                    <p style="font-weight:bold;padding-left:20px;">
                        <label>Patient Code </label>
                        <select id="spubperm-patcode" name="spubperm-patcode"></select> 
                    </p>          
                    <p style="font-weight:bold;padding-left:20px;">
                        <label>Active </label>
                        <select id="spubperm-active" name="spubperm-active">
                            <option value="Y" selected="selected">Y</option>
                            <option value="N">N</option>
                        </select>
                    </p>
                    <p>
                        <label style="font-weight:bold;padding-left:20px;">From Date </label>
                        <input id="spubperm-from" name="spubperm-from" type="date" value="<?php echo $todayDate;?>"> 
                        <label style="font-weight:bold;padding-left:20px;">To Date </label>
                        <input id="spubperm-to" name="spubperm-to" type="date" value="<?php echo $todayDate;?>"> 
                    </p>
                    <p>
                        <label style="font-weight:bold;padding-left:20px;">Attend Every </label>
                        <input id="spubperm-sunday" name="spubperm-sunday" type="checkbox" value="sunday">Sunday 
                        <input id="spubperm-monday" name="spubperm-monday" type="checkbox" value="monday">Monday 
                        <input id="spubperm-tuesday" name="spubperm-tuesday" type="checkbox" value="tuesday">Tuesday 
                        <input id="spubperm-wednesday" name="spubperm-wednesday" type="checkbox" value="wednesday">Wednesday 
                        <input id="spubperm-thursday" name="spubperm-thursday" type="checkbox" value="thursday">Thursday 
                        <input id="spubperm-friday" name="spubperm-friday" type="checkbox" value="friday">Friday 
                        <input id="spubperm-saturday" name="spubperm-saturday" type="checkbox" value="saturday">Saturday
                    </p>

                    <button id="insert" name="insert">Save</button>
                </div> 
            </div>    
        </div>    
    </div>  
    <p id="result"></p>   
    <?php require_once '../includes/pageSubFooter.php';?>
</body>
</html>
