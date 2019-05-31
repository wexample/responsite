<?php

/**
 * @param string $url
 *
 * @return array
 *
 * @throws Exception
 */
function respometer_analyse(string $url)
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
            var_dump('Url : ' . $url);
            die;
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
    $siteDomain = $report['scheme'] . '://' . $report['host'] . '/';

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

    /**
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

        $data = curl_exec($ch);
        curl_close($ch);

        return $data;
    }

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
                $actual_content_type = $this->getHeaderTag($report['header']);
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

    // We retrieve external JS files
    $files['js']['external']['numberOfResources'] = preg_match_all('@<script(?:.|\s{0,})src(?:.{0,})</script>@', $source_read, $externalScripts);

    // keep only essential things
    $externalScripts = $externalScripts[0];

    // We retrieve internal JS files
    // First step, we get all js inclusions
    preg_match_all('@<script[^<]{0,}<\/script>@', $source_read, $allJsScripts);

    // then we remove external Js files
    $internalScripts = array_diff($allJsScripts[0], $externalScripts);

    // We retrieve external CSS files
    $files['css']['external']['numberOfResources'] = preg_match_all('@<link(?:.|\s){0,}rel="stylesheet"(?:.{0,})>@U', $source_read, $externalCss);
    // keep only essential things
    $externalCss = $externalCss[0];

    // We retrieve internal CSS files
    $files['css']['internal']['numberOfResources'] = preg_match_all('@<style[^<]{0,}<\/style>@', $source_read, $internalCss);

    // keep only essential things
    $internalCss = $internalCss[0];

    // report initialization
    $files = [
        'js' => [
            'internal' => [
                'numberOfResources' => 0
            ],
            'external' => [
                'numberOfResources' => 0
            ],
            'totalSize' => 0
        ],
        'css' => [
            'internal' => [
                'numberOfResources' => 0
            ],
            'external' => [
                'numberOfResources' => 0
            ],
            'totalSize' => 0
        ]
    ];

    // We retrieve the length of the html and then we unset it to free memory
    // TODO change the strlen command with a command the calculates bytes and not a number a caracters
    $files['html'] = [
        'file' => $url,
        'size' => strlen($source_read)
    ];

    unset($source_read);

    foreach ($externalScripts as $key => &$script) {
        // We seek the position of src attribute
        $srcPosition = strpos($script, 'src=', 8) + 5;

        // We keep only what is in between the double quotes of the src attribute
        $src = substr(
            $script,
            $srcPosition,
            strpos($script, '"', $srcPosition) - $srcPosition
        );

        $src = strpos($src, '/') === 0
            ? $siteDomain . substr($src, 1)
            : $url . $src;


        // TODO change the strlen command with a command the calculates bytes and not a number a caracters
        $jsSize = strlen(curl_get_contents($src));
        $files['js']['totalSize'] += $jsSize;
        $files['js']['external'][$key] = [
            'file' => $src,
            'size' => $jsSize,
            'code' => $script
        ];
    }

    foreach ($internalScripts as $key => &$script) {
        // We seek the position of the first caracter of the script
        $scriptBeginning = strpos($script, '>');

        // TODO change the strlen command with a command the calculates bytes and not a number a caracters
        ++$files['js']['internal']['numberOfResources'];
        $files['js']['totalSize'] += $jsSize;
        $files['js']['internal'][$key] = [
            'size' => strlen(substr(
                $script,
                $scriptBeginning,
                strpos($script, '</script>', $scriptBeginning) - $scriptBeginning
            )),
            'code' => $script
        ];
    }

    unset($script);

    foreach ($externalCss as $key => &$style) {
        // We seek the position of the first caracter of the script
        $hrefPos = strpos($style, 'href="');

        // We keep only what is in between the double quotes of the src attribute
        $href = substr(
            $style,
            $hrefPos,
            strpos($style, '"', $hrefPos) - $hrefPos
        );

        $href = strpos($href, '/') === 0
            ? $siteDomain . substr($href, 1)
            : $url . $href;

        // TODO change the strlen command with a command the calculates bytes and not a number a caracters
        $cssSize = strlen(curl_get_contents($href));
        $files['css']['totalSize'] += $cssSize;
        $files['css']['external'][$key] = [
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
        $files['css']['totalSize'] += $cssSize;
        $files['css']['internal'][$key] = [
            'size' => $cssSize,
            'code' => $style
        ];
    }

    // It is safer to restore that value
    ini_set('allow_url_fopen', $oldvalue);

    return $files;
}

?>