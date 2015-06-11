<?php
	require_once 'classes/Date.php';

	$timeZone = new DateTimeZone('Asia/Kuala_Lumpur');
	$yearDate = new Date($timeZone);
    $year = $yearDate->getFullYear();
?>
<div id="footer">
    <div id="footer-divider"></div>
    <div id="footer-title">Copyright <?php echo $year;?> by Patheron Software Solutions. All Rights Reserved.</div>
</div>