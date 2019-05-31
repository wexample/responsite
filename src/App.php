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
        define('SERVER_PATH_SRC', SERVER_PATH_ROOT . 'src/');
        define('SERVER_PATH_RESOURCES', SERVER_PATH_ROOT . 'resources/');
        define('SERVER_PATH_TEMPLATE', SERVER_PATH_RESOURCES . 'template/');
        define('SERVER_PATH_SECTION', SERVER_PATH_TEMPLATE . 'section/');
        define('SERVER_PATH_BUILD', SERVER_PATH_ROOT . 'build/');
        define('SERVER_PATH_DATA', SERVER_PATH_ROOT . 'data/');

        define('CLIENT_PATH_ROOT', '/');
        define('CLIENT_PATH_BUILD', CLIENT_PATH_ROOT . 'build/');

        require SERVER_PATH_SRC . 'Site.php';
        require SERVER_PATH_ROOT . 'config/config.php';

        if (SITE_ENV === SITE_ENV_DEV)
        {
            ini_set('display_errors', 1);
            ini_set('html_errors', 1);
            ini_set('error_reporting', -1);

            require_once SERVER_PATH_ROOT . 'function/dev.php';
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
        $base = SERVER_PATH_TEMPLATE .
            preg_replace("/[^a-z\/\_]{1,}/", '', $path);
        $path = $base . '.php';

        // Reach given path.php or path/index.php.
        if (!is_file($path))
        {
            $path = $base . '/index.php';
        }

        return is_file($path) ? $this->render($path, $args) : '';
    }

    public function render(string $template, $vars = []): string
    {
        global $renderedOutput;
        global $renderedOutputActiveStack;
        global $renderBlockRegistry;
        global $templateVars;

        // Multiple rendering may occurs.
        require_once SERVER_PATH_ROOT . 'function/template.php';
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

        return $renderedOutput;
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

    public function getSite(string $name): Site
    {
        return new Site(
            $this,
            SERVER_PATH_SITE . '/' . $name . '/'
        );
    }
}