<?php

require_once SERVER_PATH_FUNCTION . 'template.php';

class Site
{
    const SITE_PREVIEW_NAME = '__preview__';

    public
        /**
         * @var \App
         */
        $app,
        /**
         * @var array
         */
        $buildingSection,
        /**
         * @var string
         */
        $clientPathBuild,
        /**
         * @var string
         */
        $clientPathAssets = '/',
        /**
         * @var array
         */
        $config,
        /**
         * @var string
         */
        $name,
        /**
         * @var string
         */
        $path,
        /**
         * @var bool
         */
        $preview,
        /**
         * @var string
         */
        $serverPathRoot,
        /**
         * @var string
         */
        $serverPathBuild;

    function __construct(App $app, string $path)
    {
        $this->app  = $app;
        $this->path = $path;
        $this->name = basename($path);

        $this->serverPathRoot  = SERVER_PATH_SITE . $this->name . '/';
        $this->clientPathBuild = CLIENT_PATH_BUILD . $this->name . '/';

        // May be changed later.
        $this->isPreview($this->path === SERVER_PATH_PREVIEW);
    }

    public function loadConfig(): void
    {
        if (true === empty($this->config))
        {
            global $loadingSite;
            $loadingSite = $this;

            $config = [];
            require $this->path . '/config.php';
            $this->config = $config;

            $count = 0;
            foreach ($this->config['section'] as &$section)
            {
                if (!isset($section['id']))
                {
                    $section['id'] = 's' . ($count++);
                }
            }

            $loadingSite = null;
        }
    }

    public function isPreview(bool $bool = false)
    {
        $this->preview = $bool;

        if ($bool)
        {
            $this->clientPathAssets = CLIENT_PATH_PREVIEW;
            $this->serverPathBuild  = SERVER_PATH_PREVIEW;
        }
        else
        {
            $dir                    = 'build/' . $this->name . '/';
            $this->clientPathAssets = '/' . $dir;
            $this->serverPathBuild  = SERVER_PATH_ROOT . $dir;
        }
    }

    public function build(string $serverPathBuild): string
    {
        global $buildingSite;
        global $pathPublicBuild;

        $this->loadConfig();

        $buildingSite    = $this;
        $pathPublicBuild = $serverPathBuild;
        $script          = $body = '';
        $style           = file_get_contents(
            SERVER_PATH_CORE . 'style/site.css'
        );
        $count           = 0;
        $specialFiles    = ['script.js', 'style.css'];

        $this->app->recreateDir($this->serverPathBuild);

        // Global style
        if (file_exists($this->path . 'style.css'))
        {
            $style .= file_get_contents($this->path . 'style.css');
        }

        foreach ($this->config['section'] as &$section)
        {
            $count++;
            $this->buildingSection = &$section;

            if (!isset($section['id']))
            {
                $section['id'] = 's' . $count;
            }

            $body   .= $this->buildSectionContent($section);
            $style  .= $this->buildSectionStyle($section);
            $script .= $this->buildSectionScript($section);

            $sectionPath = '/section/' . $section['id'];

            if (file_exists($this->path . $sectionPath))
            {
                $sitePathPage = $serverPathBuild . $sectionPath;
                if (!file_exists($sitePathPage))
                {
                    mkdir($sitePathPage, 0777, true);
                }

                $scan = new DirectoryIterator($this->path . $sectionPath);
                foreach ($scan as $fileInfo)
                {
                    $fileName = $fileInfo->getFilename();
                    if ($fileInfo->isFile() && !in_array(
                            $fileName,
                            $specialFiles
                        ))
                    {
                        copy(
                            $fileInfo->getRealPath(),
                            $sitePathPage . '/' . $fileInfo->getFilename()
                        );
                    }
                }
            }
        }

        if ('' !== $style)
        {
            file_put_contents(
                $serverPathBuild . '/style.css',
                $style
            );
        }

        // There is at least stats script.
        file_put_contents(
            $serverPathBuild . '/script.js',
            $this->wrapScript($script)
        );

        $body = $this->app->render(
            SERVER_PATH_TEMPLATE . 'layout/responsite.php',
            [
                'site'      => $this,
                'hasStyle'  => (bool) $style,
                'pageTitle' => $this->config['name'],
                'body'      => $body,
            ]
        );

        file_put_contents(
            $serverPathBuild . '/index.html',
            $body
        );

        if (isset($this->config['progressiveWebApp']))
        {
            file_put_contents(
                $serverPathBuild . '/site.webmanifest',
                json_encode($this->config['progressiveWebApp'])
            );
        }

        $buildingSite = null;

        return $body;
    }

    public function buildSectionContent(array &$section): string
    {
        $path = isset($section['template']) ?
            $this->path . 'section/' . $section['template'] . '.php' :
            SERVER_PATH_SECTION . $section['type'] . '/content.php';

        $section['data']['id'] = $section['id'];

        return $this->app->render(
            $path,
            $section['data']
        );
    }

    public function buildSectionStyle(array &$section): string
    {
        return $this->buildAssetContent($section, 'style.css') .
            // Specific part inline.
            (isset($section['style']) ? $section['style'] : '');
    }

    protected function buildAssetContent(&$section, string $assetFilename)
    {
        static $sectionHistory = [];

        $dataGeneric = '';

        // Allow to not fill up type.
        if (isset($section['type']))
        {
            // Prevent multiple inclusion.
            $type = $section['type'];
            if (!isset($sectionHistory[$assetFilename][$type]))
            {
                $sectionPath = SERVER_PATH_SECTION . $type . '/' . $assetFilename;
                $dataGeneric = is_file($sectionPath) ?
                    file_get_contents($sectionPath) : '';

                $sectionHistory[$assetFilename][$type] = $dataGeneric;
            }
            else
            {
                $dataGeneric = $sectionHistory[$assetFilename][$type];
            }
        }

        $specificPath = $this->path . '/section/' . $section['id'] . '/' . $assetFilename;

        return $dataGeneric . (is_file($specificPath) ? file_get_contents(
                $specificPath
            ) : '');
    }

    public function buildSectionScript(array &$section): string
    {
        return $this->buildAssetContent($section, 'script.js') .
            (isset($section['script']) ? $section['script'] : '');
    }

    public function wrapScript(string $script): string
    {
        return str_replace(
            '{{SITE_NAME}}',
            $this->getRenderName(),
            '(function(document, window){' .
            '"use strict";' .
            'document.addEventListener("DOMContentLoaded",function(){' .
            // Global scripts.
            file_get_contents(SERVER_PATH_CORE . 'script/site.js') .
            // Specific scrips.
            $script .
            '});})(document, window);'
        );
    }

    public function getRenderName()
    {
        return $this->preview ? self::SITE_PREVIEW_NAME : $this->name;
    }

    function uniqueId($l = 8)
    {
        return substr(md5(uniqid(mt_rand(), true)), 0, $l);
    }

    public function getUrl(): string
    {
        return CLIENT_PATH_BUILD . $this->name . '/';
    }
}