<?php

const SITE_ENV_DEV = 'dev';
# TODO Make it contextual.
const SITE_ENV = SITE_ENV_DEV;

class App
{
    public function __construct(string $callerFile = '')
    {
        define(
            'SERVER_PATH_ROOT',
            realpath(dirname($callerFile) . '/') . '/'
        );
        define('SERVER_PATH_SITE', SERVER_PATH_ROOT . 'site/');
        define('SERVER_PATH_CORE', SERVER_PATH_ROOT . 'core/');
        define('SERVER_PATH_SRC', SERVER_PATH_CORE . 'src/');
        define('SERVER_PATH_TEMPLATE', SERVER_PATH_CORE . 'template/');
        define('SERVER_PATH_ROUTE', SERVER_PATH_CORE . 'route/');
        define('SERVER_PATH_SECTION', SERVER_PATH_TEMPLATE . 'section/');
        define('SERVER_PATH_BUILD', SERVER_PATH_ROOT . 'build/');
        define('SERVER_PATH_DATA', SERVER_PATH_ROOT . 'data/');
        define('SERVER_PATH_PREVIEW', SERVER_PATH_ROOT . 'tmp/preview/');
        define('SERVER_PATH_FUNCTION', SERVER_PATH_CORE . 'function/');

        define('CLIENT_PATH_ROOT', '/');
        define('CLIENT_PATH_BUILD', CLIENT_PATH_ROOT . 'build/');
        define('CLIENT_PATH_PREVIEW', CLIENT_PATH_ROOT . 'tmp/preview/');

        require SERVER_PATH_SRC . 'Site.php';
        require SERVER_PATH_ROOT . 'config/config.php';

        if (SITE_ENV === SITE_ENV_DEV)
        {
            ini_set('display_errors', 1);
            ini_set('html_errors', 1);
            ini_set('error_reporting', -1);

            require_once SERVER_PATH_FUNCTION . 'dev.php';
        }

    }

    public function handleRequest(): string
    {
        $path = substr(parse_url($_SERVER['REQUEST_URI'])['path'], 1);

        if (!$path)
        {
            $site = new Site($this, SERVER_PATH_ROOT . 'site/default/');

            if (!isset($site->config['rootSiteFallback']) ||
                $site->config['rootSiteFallback'] === true)
            {
                return file_get_contents(
                    SERVER_PATH_BUILD . 'default/index.html'
                );
            }
            else
            {
                // No default site fallback defined.
                $path = 'fallback';
            }
        }

        return $this->loadPage(
            $path,
            [
                'requestPath' => $path,
            ]
        );
    }

    public function loadPage(string $path, $args = []): string
    {
        $base = SERVER_PATH_ROUTE .
            preg_replace("/[^a-z\/\_]{1,}/", '', $path);
        $path = $base . '.php';

        // Reach given path.php or path/index.php.
        if (!is_file($path))
        {
            $path = $base . '/index.php';
        }
        // Not found.
        if (!is_file($path))
        {
            $this->error404();
        }

        $args['app'] = $this;
        extract($args);

        return require $path;
    }

    public function error404()
    {
        http_response_code(404);
        exit;
    }

    public function renderTemplate($template, $vars = []): string
    {
        return $this->render(SERVER_PATH_TEMPLATE . $template, $vars);
    }

    public function render(string $template, $vars = []): string
    {
        global $renderedOutput;
        global $renderedOutputActiveStack;
        global $renderBlockRegistry;
        global $templateVars;

        // Multiple rendering may occurs.
        require_once SERVER_PATH_FUNCTION . 'template.php';
        require_once SERVER_PATH_SRC . 'RenderStack.php';

        // Using an object allows to keep reference through global variables.
        $renderedOutput      =
        $renderedOutputActiveStack = new RenderStack();
        $renderBlockRegistry = [];
        $templateVars        = array_merge(
            [
                'app'             => $this,
                'primaryLanguage' => 'fr',
                'metaDescription' => false,
            ],
            $vars
        );

        renderTemplate($template);

        return $this->minifyHTML($renderedOutput);
    }

    public function minifyHTML(string $content): string
    {
        return preg_replace(
            [
                '/\>[^\S ]{1,}/s',
                // strip whitespaces after tags, except space
                '/[^\S ]{1,}\</s',
                // strip whitespaces before tags, except space
                '/(\s){1,}/s',
                // shorten multiple whitespace sequences
                '/<!--(.|\s){0,}?-->/'
                // Remove HTML comments
            ],
            [
                '>',
                '<',
                '\\1',
                '',
            ],
            $content
        );
    }

    public function recreateDir(string $path): string
    {
        if (file_exists($path))
        {
            $this->removeDir($path);
        }

        mkdir($path, 0777, true);

        return $path;
    }

    public function removeDir(string $dir)
    {
        // Minimal protection.
        if ($dir === '/')
        {
            return;
        }

        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(
                $dir,
                RecursiveDirectoryIterator::SKIP_DOTS
            ),
            RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($files as $fileinfo)
        {
            $method = ($fileinfo->isDir() ? 'rmdir' : 'unlink');
            $method($fileinfo->getRealPath());
        }

        rmdir($dir);
    }

    public function isMobileDevice()
    {
        return preg_match(
            "/(android|avantgo|blackberry|bolt|boost|cricket|docomo|fone|hiptop|mini|mobi|palm|phone|pie|tablet|up\.browser|up\.link|webos|wos)/i",
            $_SERVER["HTTP_USER_AGENT"]
        );
    }

    public function getSites(): array
    {
        static $sites;

        if (true === empty($sites))
        {
            $sites = [];
            $scan  = new DirectoryIterator(SERVER_PATH_SITE);

            foreach ($scan as $fileInfo)
            {
                if (false === $fileInfo->isDot())
                {
                    $site         = $fileInfo->getFilename();
                    $sites[$site] = $this->getSite($site);
                }
            }
        }

        return $sites;
    }

    public function getSite(string $name): ?Site
    {
        return new Site(
            $this,
            SERVER_PATH_SITE . $name . '/'
        );
    }
}