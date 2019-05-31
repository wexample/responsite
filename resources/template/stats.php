<?php
/** @var \App $app */

$pathLogs    = SERVER_PATH_DATA . 'stats/' . $_GET['site'] . '/' . date('Y-m') . '.json';
$pathLogsDir = dirname($pathLogs);
$content     = [];
$key         = date('Y-m-d h');
$lang        = substr($_GET['lang'], 0, 4);

// Stats dir.
if (!file_exists($pathLogsDir))
{
    mkdir($pathLogsDir, 0777, true);
}

// Load existing dir.
if (is_file($pathLogs))
{
    $content = json_decode(file_get_contents($pathLogs), JSON_OBJECT_AS_ARRAY);
}

// Create month entry.
if (!isset($content[$key]))
{
    $content[$key] = [
        'hits'   => 0,
        'mobile' => 0,
        'lang'   => [],
    ];
}

// Update data.
$content[$key]['hits']++;
$content[$key]['mobile']      += (bool) $_GET['mobile'] ? 1 : -1;
$content[$key]['lang'][$lang] = isset($content[$key]['lang'][$lang]) ? $content[$key]['lang'][$lang] + 1 : 1;

file_put_contents($pathLogs, json_encode($content), JSON_PRETTY_PRINT);
