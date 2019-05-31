<?php

/**
 * @param string      $name
 * @param string|bool $inlineData can ben bool for auto close, or inline string.
 */
function block(string $name, $inlineData = false)
{
    global $renderedOutputActiveStack;
    global $renderBlockRegistry;

    // Block is overriding a parent block.
    if (isset($renderBlockRegistry[$name]) === true)
    {
        // Buffered content has no sense, so we trash it.
        ob_get_clean();

        $renderedOutputActiveStack = $renderBlockRegistry[$name];
        // Save content for parent() call.
        $renderedOutputActiveStack->overridenStackContent = (array) $renderedOutputActiveStack->stack;
        // Remove rendered content.
        $renderedOutputActiveStack->stack = [];
    }
    // Block is a new one.
    else
    {
        renderRecordStop();

        $renderedOutputActiveStack       =
            new RenderStack($renderedOutputActiveStack);
        $renderedOutputActiveStack->name = $name;
    }

    $renderBlockRegistry[$name] = $renderedOutputActiveStack;

    renderRecordStart();

    if ($inlineData === true)
    {
        if (is_string($inlineData) === true)
        {
            echo $inlineData;
        }
        endblock();
    }
}

function endblock()
{
    global $renderedOutputActiveStack;

    renderRecordStop($renderedOutputActiveStack->name);

    $parent = $renderedOutputActiveStack->parent;

    $parent->stack[$renderedOutputActiveStack->name] = $renderedOutputActiveStack;
    $renderedOutputActiveStack                       = $parent;
    renderRecordStart();
}

function extend(string $name)
{
    global $renderPathTemplateCurrent;

    if (substr($name, 0, 10) === 'template::')
    {
        $path = SERVER_PATH_TEMPLATE . substr($name, 10) . '.php';
    }
    else
    {
        $path = $renderPathTemplateCurrent . $name . '.php';
    }
    renderTemplate($path);
}

function get(string $var)
{
    global $templateVars;
    return $templateVars[$var];
}

function inc(string $path, $absolute = false)
{
    global $renderPathTemplateCurrent;
    global $templateVars;

    extract($templateVars);

    if ($absolute)
    {
        require $path;
    }
    else
    {
        require $renderPathTemplateCurrent . $path . '.php';
    }
}

function page($page)
{
    /** @var Site */
    global $buildingSite;

    return '/exec?site=' . $buildingSite->name . '&section=' . $buildingSite->buildingSection['id'] . '&page=' . $page;
}

function parse(string $text)
{
    if (substr($text, 0, 6) === 'site::')
    {
        /** @var Site */
        global $buildingSite;
        inc(str_replace('site::', $buildingSite->path . '/', $text), true);
        return;
    }

    echo $text;
}

function path($path)
{
    /** @var Site */
    global $buildingSite;

    if (substr($path, 0, 4) !== 'http')
    {
        return $buildingSite->clientPathAssets . $path;
    }
}

function parent()
{
    global $renderedOutputActiveStack;

    renderRecordStop();
    $renderedOutputActiveStack->stack = array_merge(
        $renderedOutputActiveStack->stack,
        $renderedOutputActiveStack->overridenStackContent
    );
    // Replace item that have the base stack name.
    if (isset($renderedOutputActiveStack->stack[$renderedOutputActiveStack->name]))
    {
        $renderedOutputActiveStack->stack[] = $renderedOutputActiveStack->stack[$renderedOutputActiveStack->name];
        unset($renderedOutputActiveStack->stack[$renderedOutputActiveStack->name]);
    }
    renderRecordStart();
}

function renderRecordStart()
{
    ob_start();
}

function renderRecordStop($key = null)
{
    /** @var $renderedOutputActiveStack \RenderStack */
    global $renderedOutputActiveStack;
    $renderedOutputActiveStack->appendString(trim(ob_get_clean()), $key);
}

function renderTemplate(string $path)
{
    global $templateVars;
    global $renderPathTemplateCurrent;

    $pathPrevious              = $renderPathTemplateCurrent;
    $renderPathTemplateCurrent = dirname($path) . '/';

    extract($templateVars);
    renderRecordStart();
    require $path;
    renderRecordStop();

    $renderPathTemplateCurrent = $pathPrevious;
}

function set($var, $val)
{
    global $templateVars;
    $templateVars[$var] = $val;
}

function siteURL()
{
    return ((empty($_SERVER['HTTPS']) === false && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] === 443
            ? 'https://'
            : 'http://')
        . (isset($_SERVER['HTTP_HOST']) === false ? $_SERVER['HTTP_HOST'] : $_SERVER['SERVER_NAME']) . '/';
}
