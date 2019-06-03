<?php

if (isset($_GET['siteToAnalyze']) === false || $_GET['siteToAnalyze'] === '') {
    throw new Exception('No site to analyze');
}

$siteToAnalyze = $_GET['siteToAnalyze'];

require SERVER_PATH_ROOT . 'function/respometer.php';

// TODO implement a try/catch
[$content, $report] = getContentFromUrl($siteToAnalyze);
$siteDomain = $report['scheme'] . '://' . $report['host'] . '/';

analyze(
    $content,
    $siteToAnalyze,
    $siteDomain,
    SERVER_PATH_ROOT . 'data/stats/external/' . $report['host']
);

require_once SERVER_PATH_ROOT . 'function/template.php';

goBack('?siteToAnalyze=' . $siteDomain . '&host=' . $report['host']);
