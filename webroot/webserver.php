<?php
if (file_exists(__DIR__ . '/' . $_SERVER['SCRIPT_NAME'])) {
	return false; // serve the requested resource as-is.
} else {
	$_GET['url'] = $_REQUEST['url'] = $_SERVER['REQUEST_URI'];
	include_once 'index.php';
}