<?php

namespace dvzCodeTags;

function getCodeFormatterInstance(string $type, bool $initialize = false)
{
    if (isset($GLOBALS['dvzCodeTagsCodeFormatterInstances'][$type])) {
        return $GLOBALS['dvzCodeTagsCodeFormatterInstances'][$type];
    } else {
        if ($initialize == true) {
            $directory = MYBB_ROOT . 'inc/plugins/dvz_code_tags/codeFormatters/';

            $name = \dvzCodeTags\getSettingValue($type . '_code_formatter');
            $className = ucfirst($name) . 'CodeFormatter';
            $class = '\dvzCodeTags\Formatting\\' . $className;

            require_once $directory . $className . '.php';

            $GLOBALS['dvzCodeTagsCodeFormatterInstances'][$type] = new $class;

            return $GLOBALS['dvzCodeTagsCodeFormatterInstances'][$type];
        } else {
            return null;
        }
    }
}

function getAvailableCodeFormatterNames(): array
{
    static $result;

    if (!$result) {
        $directory = MYBB_ROOT . 'inc/plugins/dvz_code_tags/codeFormatters/';

        $result = array_map(
            function ($path) {
                return str_replace('CodeFormatter.php', null, basename($path));
            },
            glob($directory . '*CodeFormatter.php')
        );
    }

    return $result;
}

function getAvailableCodeFormatterNamesByType(): array
{
    $formatterNamesByType = [
        'inline' => [],
        'block' => [],
    ];

    $formatters = \dvzCodeTags\getAvailableCodeFormatters();

    $directory = MYBB_ROOT . 'inc/plugins/dvz_code_tags/codeFormatters/';

    foreach ($formatters as $formatterName) {
        $className = $formatterName . 'CodeFormatter';

        require_once $directory . $className . '.php';

        $interfaces = class_implements($className);

        if (in_array(\dvzCodeTags\InlineCodeFormatter, $interfaces)) {
            $formatterNamesByType['inline'][] = $formatterName;
        }

        if (in_array(\dvzCodeTags\BlockCodeFormatter, $interfaces)) {
            $formatterNamesByType['block'][] = $formatterName;
        }
    }

    return $formatterNamesByType;
}

function getAvailableCodeFormattersSettings(): array
{
    $settings = [];

    $formatters = \dvzCodeTags\getAvailableCodeFormatterNames();

    $directory = MYBB_ROOT . 'inc/plugins/dvz_code_tags/codeFormatters/';

    foreach ($formatters as $formatterName) {
        $className = $formatterName . 'CodeFormatter';

        require_once $directory . $className . '.php';

        $settings = array_merge($settings, ('\dvzCodeTags\Formatting\\' . $className)::getSettingDefinitions());
    }

    return $settings;
}

// common
function isStaticRender(): bool
{
    static $status;

    if (!$status) {
        $status = !defined('THIS_SCRIPT') || !in_array(THIS_SCRIPT, [
            'xmlhttp.php',
            'newreply.php',
        ]);
    }

    return $status;
}

function addHooksNamespace(string $namespace): void
{
    global $plugins;

    $namespaceLowercase = strtolower($namespace);
    $definedUserFunctions = get_defined_functions()['user'];

    foreach ($definedUserFunctions as $callable) {
        $namespaceWithPrefixLength = strlen($namespaceLowercase) + 1;
        if (substr($callable, 0, $namespaceWithPrefixLength) == $namespaceLowercase . '\\') {
            $hookName = substr_replace($callable, null, 0, $namespaceWithPrefixLength);

            $plugins->add_hook($hookName, $namespace . '\\' . $hookName);
        }
    }
}

function getSettingValue(string $name): string
{
    global $mybb;
    return $mybb->settings['dvz_code_tags_' . $name];
}

function getCsvSettingValues(string $name): array
{
    static $values;

    if (!isset($values[$name])) {
        $values[$name] = array_filter(explode(',', getSettingValue($name)));
    }

    return $values[$name];
}

function getDelimitedSettingValues(string $name): array
{
    static $values;
    if (!isset($values[$name])) {
        $values[$name] = array_filter(preg_split("/\\r\\n|\\r|\\n/", getSettingValue($name)));
    }
    return $values[$name];
}
