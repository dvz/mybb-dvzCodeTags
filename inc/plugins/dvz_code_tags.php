<?php
/**
 * Copyright (c) 2017-2019, Tomasz 'Devilshakerz' Mlynski [devilshakerz.com]
 *
 * Permission to use, copy, modify, and/or distribute this software for any purpose with or without fee is hereby
 * granted, provided that the above copyright notice and this permission notice appear in all copies.
 *
 * THE SOFTWARE IS PROVIDED "AS IS" AND THE AUTHOR DISCLAIMS ALL WARRANTIES WITH REGARD TO THIS SOFTWARE INCLUDING ALL
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS. IN NO EVENT SHALL THE AUTHOR BE LIABLE FOR ANY SPECIAL, DIRECT,
 * INDIRECT, OR CONSEQUENTIAL DAMAGES OR ANY DAMAGES WHATSOEVER RESULTING FROM LOSS OF USE, DATA OR PROFITS, WHETHER IN
 * AN ACTION OF CONTRACT, NEGLIGENCE OR OTHER TORTIOUS ACTION, ARISING OUT OF OR IN CONNECTION WITH THE USE OR
 * PERFORMANCE OF THIS SOFTWARE.
 */

// common modules
require_once MYBB_ROOT . 'inc/plugins/dvz_code_tags/core.php';
require_once MYBB_ROOT . 'inc/plugins/dvz_code_tags/formatting.php';
require_once MYBB_ROOT . 'inc/plugins/dvz_code_tags/parsing.php';
require_once MYBB_ROOT . 'inc/plugins/dvz_code_tags/CodeFormatterInterface.php';
require_once MYBB_ROOT . 'inc/plugins/dvz_code_tags/InlineCodeFormatterInterface.php';
require_once MYBB_ROOT . 'inc/plugins/dvz_code_tags/BlockCodeFormatterInterface.php';

// hook files
require_once MYBB_ROOT . 'inc/plugins/dvz_code_tags/hooks_frontend.php';

// hooks
\dvzCodeTags\addHooksNamespace('dvzCodeTags\Hooks');

// init
define('dvzCodeTags\PARSER_EXCLUSIVE_CHAR', "\0");

$GLOBALS['dvzCodeTagsPlaceholders'] = [];
$GLOBALS['dvzCodeTagsCodeFormatterInstances'] = [];

// MyBB plugin system
function dvz_code_tags_info()
{
    global $lang;

    $lang->load('dvz_code_tags');

    return [
        'name'          => 'DVZ Code Tags',
        'description'   => $lang->dvz_code_tags_description,
        'website'       => 'https://devilshakerz.com/',
        'author'        => 'Tomasz \'Devilshakerz\' Mlynski',
        'authorsite'    => 'https://devilshakerz.com/',
        'version'       => '1.0.2',
        'codename'      => 'dvz_code_tags',
        'compatibility' => '18*',
    ];
}

function dvz_code_tags_install()
{
    global $db, $cache, $PL;

    dvz_code_tags_admin_load_pluginlibrary();

    // settings
    $coreSettings = [
        'parse_block_fenced_code' => [
            'title'       => 'Parse fenced block code',
            'description' => 'Choose whether to parse code blocks within <code>```</code> tags.',
            'optionscode' => 'yesno',
            'value'       => '1',
        ],
        'parse_block_mycode_code' => [
            'title'       => 'Parse MyCode block code',
            'description' => 'Choose whether to parse code blocks within <code>[code]</code> tags.',
            'optionscode' => 'yesno',
            'value'       => '1',
        ],
        'parse_inline_backticks_code' => [
            'title'       => 'Parse inline code (backticks)',
            'description' => 'Choose whether to parse code enclosed in <code>`backticks`</code>.',
            'optionscode' => 'yesno',
            'value'       => '1',
        ],
        'bypass_wordfilters' => [
            'title'       => 'Bypass Word Filters in code',
            'description' => 'Choose whether Word Filters should be ignored within code tags.',
            'optionscode' => 'select
nowhere=Apply Word Filters everywhere
block=Bypass in block code
everywhere=Bypass in block and inline code',
            'value'       => 'block',
        ],
        'inline_code_formatter' => [
            'title'       => 'Inline code formatter',
            'description' => 'Enter name of the inline code formatter included in plugin\'s directory.',
            'optionscode' => 'text',
            'value'       => 'DefaultInline',
        ],
        'block_code_formatter' => [
            'title'       => 'Block code formatter',
            'description' => 'Enter name of the block code formatter included in plugin\'s directory.',
            'optionscode' => 'text',
            'value'       => 'Prismjs',
        ],
    ];

    $availableCodeFormatters = \dvzCodeTags\getAvailableCodeFormatterNames();
    $availableCodeFormattersSettings = \dvzCodeTags\getAvailableCodeFormattersSettings();

    // have core settings first overwriting conflicting names
    $settings = array_merge($coreSettings, $availableCodeFormattersSettings, $coreSettings);

    $PL->settings(
        'dvz_code_tags',
        'DVZ Code Tags',
        'Settings for DVZ Code Tags.',
        $settings
    );

    // datacache
    $cache->update('dvz_code_tags', [
        'version' => dvz_code_tags_info()['version'],
        'codeFormattersInstalled' => $availableCodeFormatters,
    ]);
}

function dvz_code_tags_uninstall()
{
    global $db, $cache, $PL;

    dvz_code_tags_admin_load_pluginlibrary();

    // settings
    $PL->settings_delete('dvz_code_tags', true);

    // datacache
    $cache->delete('dvz_code_tags');
}

function dvz_code_tags_is_installed()
{
    global $db;

    // manual check to avoid caching issues
    $query = $db->simple_select('settinggroups', 'gid', "name='dvz_code_tags'");

    return (bool)$db->num_rows($query);
}

function dvz_code_tags_activate()
{
    global $cache, $PL;

    dvz_code_tags_admin_load_pluginlibrary();

    // stylesheets
    $stylesheets = [
        'dvz_code_tags' => [],
    ];

    foreach ($stylesheets as $stylesheetName => $stylesheet) {
        $PL->stylesheet(
            $stylesheetName,
            file_get_contents(MYBB_ROOT . 'inc/plugins/dvz_code_tags/stylesheets/' . $stylesheetName . '.css'),
            $stylesheet['attached_to'] ?? null
        );
    }

    // datacache
    $pluginCache = $cache->read('dvz_code_tags');

    if (isset($pluginCache['version']) && version_compare($pluginCache['version'], dvz_code_tags_info()['version']) == -1) {
        $pluginCache['version'] = dvz_code_tags_info()['version'];

        $cache->update('dvz_code_tags', $pluginCache);
    }
}

function dvz_code_tags_deactivate()
{
    global $PL;

    dvz_code_tags_admin_load_pluginlibrary();

    // stylesheets
    $PL->stylesheet_delete('dvz_code_tags');
}

// helpers
function dvz_code_tags_admin_load_pluginlibrary()
{
    global $lang;

    if (!defined('PLUGINLIBRARY')) {
        define('PLUGINLIBRARY', MYBB_ROOT . 'inc/plugins/pluginlibrary.php');
    }

    if (!file_exists(PLUGINLIBRARY)) {
        $lang->load('dvz_code_tags');

        flash_message($lang->dvz_code_tags_admin_pluginlibrary_missing, 'error');

        admin_redirect('index.php?module=config-plugins');
    } elseif (!$PL) {
        require_once PLUGINLIBRARY;
    }
}
