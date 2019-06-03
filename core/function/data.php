<?php

/**
 * Return given site dir and create it if missing.
 *
 * @param \Site $site
 *
 * @return string
 */
function site_data_dir(Site $site): string
{
    $dir = SERVER_PATH_DATA . $site->getRenderName();
    if (!file_exists($dir))
    {
        mkdir($dir);
    }

    return $dir;
}

function site_data_save(Site $site, string $name, array $data)
{
    $dir = site_data_dir($site);

    file_put_contents($dir . '/' . $name . '.json', json_encode($data));
}

function site_data_append(Site $site, string $name, array $data) {
    site_data_save($site, $name, array_merge(site_data_load($site, $name),$data));
}

function site_data_load(Site $site, string $name, $default = []): array
{
    $dir      = site_data_dir($site);
    $filePath = $dir . '/' . $name . '.json';

    if (is_file($filePath))
    {
        return json_decode(file_get_contents($filePath, JSON_OBJECT_AS_ARRAY));
    }
    else
    {
        return $default;
    }
}