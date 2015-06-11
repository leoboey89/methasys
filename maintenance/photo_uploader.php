<?php
	// current working directory
	$selfDirectory = str_replace('maintenance/' . basename($_SERVER['PHP_SELF']), '', $_SERVER['PHP_SELF']);

	// directory that receive the uploaded files
	$uploadDirectory = $_SERVER['DOCUMENT_ROOT'] . $selfDirectory . 'photos/';

	// location of the upload photo form
	$uploadForm = 'http://' . $_SERVER['HTTP_HOST'] . $selfDirectory . 'maintenance/upload_photo.php';

	// location of the upload success form
	$uploadSuccess = 'http://' . $_SERVER['HTTP_HOST'] . $selfDirectory . 'maintenance/upload_success.php';

	// name of the filename
	$filename = 'file';

	// possible PHP upload errors
	$errors = array(1 => 'max file size exceeded', 
                	2 => 'max file size exceeded', 
                	3 => 'file only upload partially', 
                	4 => 'no file was chosen');

	isset($_POST['submit'])
		or error('The upload form is neaded', $uploadForm);

	foreach ($_FILES["file"]["name"] as $fn => $name) {
		$imageFileType = pathinfo($name,PATHINFO_EXTENSION);

		// check standard uploading errors
		if ($_FILES[$filename]['error'][$fn] != 0) {
			error($errors[$_FILES[$filename]['error'][$fn]], $uploadForm);
		}
		
		// validate file on HTTP upload
		if (!is_uploaded_file($_FILES[$filename]['tmp_name'][$fn])) {
			error('Not an HTTP upload', $uploadForm);
		}

		// make sure the upload is an image
		if (!getimagesize($_FILES[$filename]['tmp_name'][$fn])) {
			error('Only image uploads are allowed', $uploadForm);
		}

		// Only allow jpg format
		if($imageFileType != "jpg") {
		    error('Only jpg format is allowed', $uploadForm);
		}

		// Get upload file name
		$uploadFilename = $uploadDirectory.$_FILES[$filename]['name'][$fn];

		// now let's move the file to its final and allocate it with the new filename
		if (!move_uploaded_file($_FILES[$filename]['tmp_name'][$fn], $uploadFilename)) {
			error('Insuffiecient permission on server', $uploadForm);
		}
	}

	// If you got this far, everything has worked and the file has been successfully saved.
	// We are now going to redirect the client to the success page.
	header('Location: ' . $uploadSuccess);

	// make an error handler which will be used if the upload fails
	function error($error, $location, $seconds = 3)
	{
		header("Refresh: $seconds; URL=\"$location\"");
		echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN"'."\n".
		'"http://www.w3.org/TR/html4/strict.dtd">'."\n\n".
		'<html lang="en">'."\n".
		'	<head>'."\n".
		'		<meta http-equiv="content-type" content="text/html; charset=iso-8859-1">'."\n\n".
		'		<link rel="stylesheet" type="text/css" href="stylesheet.css">'."\n\n".
		'	<title>Upload error</title>'."\n\n".
		'	</head>'."\n\n".
		'	<body>'."\n\n".
		'	<div id="Upload">'."\n\n".
		'		<h1>Upload failure</h1>'."\n\n".
		'		<p>An error has occured: '."\n\n".
		'		<span style="color:red">' . $error . '...</span>'."\n\n".
		'	 	The upload form is reloading</p>'."\n\n".
		'	 </div>'."\n\n".
		'</html>';
		exit;
	}
	
?>