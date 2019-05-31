<?php

if (isset($_GET['siteToAnalyze']) === false || $_GET['siteToAnalyze'] === '') {
    throw new Exception('No site to analyze');
}

$siteToAnalyze = $_GET['siteToAnalyze'];

if (isset($_SESSION) === false) {
    session_start();
}

require SERVER_PATH_ROOT . 'function/respometer.php';

// TODO implement a try/catch
$_SESSION['respometer'] = respometer_analyse($siteToAnalyze);
$_SESSION['analyzedSite'] = $siteToAnalyze;

// Go back to the previous page
header('location:' . $_SERVER['HTTP_REFERER']);

?>