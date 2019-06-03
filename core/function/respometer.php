<?php

/**
 * Retrieving content from a url via curl.
 *
 * @param string $url
 *
 * @return string
 */
function curl_get_contents(string &$url): string
{
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);

    // To be trusted as a classic browser
    curl_setopt(
        $ch,
        CURLOPT_USERAGENT,
        'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Ubuntu Chromium/74.0.3729.169 Chrome/74.0.3729.169 Safari/537.36'
    );

    $data = curl_exec($ch);
    curl_close($ch);

    return $data;
}

/**
 * @param string $url
 *
 * @return array
 *
 * @throws Exception
 */
function getContentFromUrl(string $url)
{
    // Force this value in order to make localhost calls works
    // TODO condition with dev environment
    $oldvalue = ini_get('allow_url_fopen');
    ini_set('allow_url_fopen', 'On');

    if ($url[-1] !== '/') {
        $url .= '/';
    }

    // Retrieves the scheme, host, user, pass, query, and port
    // If there is already a protocol...(look at the beginning of the url)
    if (false === strpos(substr($url, 0, 10), '://')) {
        $report = parse_url(
            (443 === parse_url($url, 2)
                ? 'https://'
                : 'http://'
            ) . $url);
    } else
        $report = parse_url($url);

    if (array_key_exists('host', $report) === false) {
        $report['host'] = '';
    }

    if (array_key_exists('user', $report) === false) {
        $report['user'] = '';
    }

    if (array_key_exists('pass', $report) === false) {
        $report['pass'] = '';
    }

    if (array_key_exists('path', $report) === false) {
        $report['path'] = '/';
    }

    $report['query'] = (array_key_exists('query', $report) === false)
        ? ''
        : '?' . $report['query'];

    if (array_key_exists('port', $report) === false) {
        if (array_key_exists('scheme', $report) === false) {
            throw new Exception('Problem with scheme !');
        }

        if ($report['scheme'] === 'http') {
            $report['port'] = 80;
        } else if ($report['scheme'] === 'https') {
            $report['port'] = 443;
        }
    }

    $ip = gethostbyname($report['host']);

    // SSL or normal connect
    $host_str = ($report['scheme'] === 'https')
        ? 'ssl://' . $ip
        : $ip;

    $source_read = '';

    $context = stream_context_create(array(
            'http' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
            ),
        )
    );

    // TODO fix the warning and remove the @. The warning is SSL related.
    $socket = @stream_socket_client(
        $host_str,
        $errno,
        $errstr,
        ini_get("default_socket_timeout"),
        STREAM_CLIENT_CONNECT,
        $context
    );

    $actual_content_type = '';

    if ($socket) {

        /**
         * function gets a special 'header-tag' form a header ('Content-Type', 'Content-Size' or whatever)
         *
         * @param string $header
         *
         * @return string
         */
        function getHeaderTag(string &$header): string
        {
            preg_match_all('/((?i)content-type:).*[' . "\n]/", $header, $regs);

            if (isset($regs[0][0])) {
                $hit = preg_replace('/((?i)content-type:)/', '', $regs[0][0]);
                return trim(strtolower($hit));
            }

            return '';
        }

        $report['header_send'] = 'GET ' . $report['path'] . $report['query'] . ' HTTP/1.0' . PHP_EOL
            . 'HOST: ' . $report['host'] . PHP_EOL
            . 'User-Agent: SEOCrawl' . PHP_EOL
            . 'Connection: close' . PHP_EOL . PHP_EOL;

        // Now send the header
        fputs($socket, $report['header_send']);

        $status = socket_get_status($socket);

        $header_found = false;

        while (isset($stop) === false) {
            socket_set_timeout($socket, 4); // in seconds

            // The @ is to avoid the strange "SSL fatal protocol error"-warning that
            // appears in some environments without any reasons
            $line_read = @fgets($socket, 4096);
            $source_read .= $line_read; // do this anyway

            if (false == $header_found && "\r\n\r\n" == substr($source_read, -4, 4)) {
                $report['header'] = substr($source_read, 0, strlen($source_read) - 2);
                $actual_content_type = getHeaderTag($report['header']);
                $source_read = '';
                $header_found = true;

                // Header found here -> check if source should be followed (content-type)
                $report['received_completely'] = true;
            } // end cut and handle header

            $status = socket_get_status($socket);

            // Check timeout
            if (array_key_exists('timed_out', $status) === true) {
                echo 'Socket stream timed out<br>';
                $stop = true;
                $report['received_completely'] = false;
            }

            // Check eof
            if (array_key_exists('eof', $status) === true) {
                $stop = true;
            }
        }
        fclose($socket); // close socket
    } else {
        $source_read = curl_get_contents($url);

        if ($source_read === false) {
            $source_read = file_get_contents($url, false, $context);
        }

        if ($source_read === false) {
            throw new Exception(printf(
                'Connection problem' . '<br/>'
                . "%s : %s" . '<br/>'
                . "URL : %s"
                . '<br/>' . "Host_str : %s"
                . '<br/>' . 'Report : '
                . "<br/>%s",
                $errno,
                $errstr,
                $url,
                $host_str,
                nl2br(print_r($report, true))
            ));
        }
    }

    // It is safer to restore that value
    ini_set('allow_url_fopen', $oldvalue);

    return [$source_read, $report];
}

/**
 * @param string $url
 * @param string $site
 * @param string $siteDomain
 *
 * @return string
 */
function getAbsoluteUrl(string &$url, string &$site, string &$siteDomain) {
    if (strpos($url, 'http') !== 0)
    {
        $url = strpos($url, '/') === 0
            ? $siteDomain . substr($url, 1)
            : $site . $url;
    }
}

/**
 * Analyzes a website content. Stores the corresponding report in a file and returns the report.
 *
 * @param string $content
 * @param string $url
 * @param string $siteDomain
 * @param string $destinationFolder
 *
 * @return array
 */
function analyze (string $content, string $url, string $siteDomain, string $destinationFolder) : array {
    // report initialization
    $report = [
        'totalSize' => 0,
        'js' => [
            'internal' => [
                'count' => 0
            ],
            'external' => [
                'count' => 0
            ],
            'totalSize' => 0
        ],
        'css' => [
            'internal' => [
                'count' => 0
            ],
            'external' => [
                'count' => 0
            ],
            'totalSize' => 0
        ],
        'images' => [
            'totalSize' => 0,
            'count' => 0
        ]
    ];

    // We retrieve external JS files
    $report['js']['external']['count'] = preg_match_all('@<script(?:.|\s{0,})src(?:.{0,})</script>@', $content, $externalScripts);

    // We retrieve internal JS files
    // First step, we get all js inclusions
    preg_match_all('@<script[^<]{0,}<\/script>@', $content, $allJsScripts);

    // We retrieve external CSS files
    $report['css']['external']['count'] = preg_match_all('@<link(?:.|\s){0,}rel="stylesheet"(?:.{0,})>@U', $content, $externalCss);

    // We retrieve internal CSS files
    $report['css']['internal']['count'] = preg_match_all('@<style[^<]{0,}<\/style>@', $content, $internalCss);

    // We retrieve images
    $report['images']['count'] = preg_match_all('@<img[^>]{0,}>@', $content, $images);

    // We retrieve the length of the html and then we unset it to free memory
    // TODO change the strlen command with a command the calculates bytes and not a number a caracters
    $report['html'] = [
        'file' => $url,
        'size' => strlen($content)
    ];

    unset($content);

    // Now that we get all the stuff we need from the source, we free the memory

    // Processing external JS files ...
    if ($report['js']['external']['count'] === false) {
        $report['js']['external']['count'] = 0;
    }

    // keep only essential things
    $externalScripts = $externalScripts[0];

    // Processing internal JS files ...
    // In the first step, we have had all js inclusions. Now, we remove external Js files
    $internalScripts = array_diff($allJsScripts[0], $externalScripts);

    // Processing external CSS files
    if ($report['css']['external']['count'] === false) {
        $report['css']['external']['count'] = 0;
    }

    // keep only essential things
    $externalCss = $externalCss[0];

    // Processing internal CSS files
    if ($report['css']['internal']['count'] === false) {
        $report['css']['internal']['count'] = 0;
    }

    // keep only essential things
    $internalCss = $internalCss[0];

    // Processing images ...
    if ($report['images']['count'] === false) {
        $report['images']['count'] = 0;
    }

    // keep only essential things
    $images = $images[0];

    $report['totalSize'] += $report['html']['size'];

    foreach ($externalScripts as $key => &$script) {
        // We seek the position of src attribute
        $srcPosition = strpos($script, 'src=', 8) + 5;

        // We keep only what is in between the double quotes of the src attribute
        $src = substr(
            $script,
            $srcPosition,
            strpos($script, '"', $srcPosition) - $srcPosition
        );

        // TODO change the strlen command with a command the calculates bytes and not a number a caracters
        $jsSize = strlen(curl_get_contents($src));
        $report['js']['totalSize'] += $jsSize;
        $report['js']['external'][$key] = [
            'file' => $src,
            'size' => $jsSize,
            'code' => $script
        ];
    }

    foreach ($internalScripts as $key => &$script) {
        // We seek the position of the first caracter of the script
        $scriptBeginning = strpos($script, '>');

        // TODO change the strlen command with a command the calculates bytes and not a number a caracters
        ++$report['js']['internal']['count'];
        $jsSize = strlen(substr(
            $script,
            $scriptBeginning,
            strpos($script, '</script>', $scriptBeginning) - $scriptBeginning
        ));

        $report['js']['totalSize'] += $jsSize;
        $report['js']['internal'][$key] = [
            'size' => $jsSize,
            'code' => $script
        ];
    }

    unset($script);

    $report['totalSize'] += $report['js']['totalSize'];

    foreach ($externalCss as $key => &$style) {
        // We seek the position of the first caracter of the script
        $hrefPos = strpos($style, 'href="') + 6;

        // We keep only what is in between the double quotes of the src attribute
        $href = substr(
            $style,
            $hrefPos,
            strpos($style, '"', $hrefPos) - $hrefPos
        );

        getAbsoluteUrl($href, $url, $siteDomain);

        // TODO change the strlen command with a command the calculates bytes and not a number a caracters
        $cssSize = strlen(curl_get_contents($href));
        $report['css']['totalSize'] += $cssSize;
        $report['css']['external'][$key] = [
            'file' => $href,
            'size' => $cssSize,
            'code' => $style
        ];
    }

    foreach ($internalCss as $key => &$style) {
        // We seek the position of the first caracter of the script
        $styleBeginning = strpos($style, '>') + 1;

        // We keep only the body of the style markup
        $cssSize = strlen(substr(
            $style,
            $styleBeginning,
            strpos($style, '<', $styleBeginning) - $styleBeginning
        ));
        $report['css']['totalSize'] += $cssSize;
        $report['css']['internal'][$key] = [
            'size' => $cssSize,
            'code' => $style
        ];
    }

    $report['totalSize'] += $report['css']['totalSize'];

    foreach ($images as $key => &$image)
    {
        $srcPos = strpos($image, 'src=') + 5;

        // img tags may initially not have a src attribute
        if ($srcPos !== false) {
            $file = substr($image, $srcPos, strpos($image, '"', $srcPos) - $srcPos);
            $imageSize = strlen(curl_get_contents($image));
        } else {
            $file = 'Pas de fichier détecté';
            $imageSize = 0;
        }

        $report['images']['totalSize'] += $imageSize;
        $report['images'][$key] = [
            'code' => $image,
            'file' => $file,
            'size' => $imageSize
        ];
    }

    $report['totalSize'] += $report['images']['totalSize'];

    // Calculation of wexIndex for the respometer
    define('BEST_SITE_SIZE', 19376);
    define('WORST_SITE_SIZE', 1315598); // 2 billion

    $report['index'] = number_format(
        100 * (1 - (($report['totalSize'] - BEST_SITE_SIZE) / (WORST_SITE_SIZE - BEST_SITE_SIZE))),
        2,
        ',',
        ' '
    );

    if (((int) $report['index']) >= 100) {
        $report['index'] = 100;
    } elseif ((int) $report['index'] <= 0)
    {
        $report['index'] = 0;
    }

    // We now includes the two
    $report['worstRankedSite'] = WORST_SITE_SIZE;
    $report['bestRankedSite'] = BEST_SITE_SIZE;

    // Report creation
    if (file_exists($destinationFolder) === false) {
        mkdir($destinationFolder, 0777, true);
    }

    $filePointer = fopen($destinationFolder . '/report.php', 'w');
    fwrite($filePointer, '<?php $report= ' . var_export($report, true) . ' ?>');
    fclose($filePointer);

    return $report;
}