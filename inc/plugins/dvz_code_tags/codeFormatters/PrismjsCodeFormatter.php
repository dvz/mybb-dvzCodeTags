<?php

namespace dvzCodeTags\Formatting;

class PrismjsCodeFormatter implements BlockCodeFormatter
{
    public function getHeadEndHtml(): ?string
    {
        $directories = $this->getDirectoryPaths();

        $headHtml = null;

        $cssFiles = \dvzCodeTags\getDelimitedSettingValues('prismjs_code_formatter_css_files');

        foreach ($cssFiles as $file) {
            $data = explode(' ', $file);

            if (isset($data[1])) {
                $additionalAttributes = ' integrity="' . $data[1] . '" crossorigin="anonymous"';
            } else {
                $additionalAttributes = null;
            }

            $headHtml .= '<link type="text/css" rel="stylesheet" href="' . $directories['resources'] . $data[0] . '"' . $additionalAttributes . ' />' . PHP_EOL;
        }

        return $headHtml;
    }

    public function getBodyEndHtml(): ?string
    {
        global $mybb, $lang;

        $lang->load('dvz_code_tags');

        $directories = $this->getDirectoryPaths();

        $bodyHtml = null;

        $jsFiles = \dvzCodeTags\getDelimitedSettingValues('prismjs_code_formatter_javascript_files');

        foreach ($jsFiles as $file) {
            $data = explode(' ', $file);

            $attributes = [];

            $attributes[] = 'src="' . $directories['resources'] . $data[0] . '"';

            if (in_array($data[0], ['prism.min.js', 'prism.js'])) {
                $attributes[] = 'data-manual';
            }

            if (isset($data[1])) {
                $attributes[] = 'integrity="' . $data[1] . '"';
                $attributes[] = 'crossorigin="anonymous"';
            }

            $bodyHtml .= '<script ' . implode(' ', $attributes) . '></script>' . PHP_EOL;
        }

        $optionsJson = json_encode([
            'components_directory' => \htmlspecialchars_uni($directories['components']),
        ]);

        $langJson = json_encode([
            'dvz_code_tags_select_all' => $lang->dvz_code_tags_select_all,
        ]);

        $bodyHtml .= '<script src="' . $mybb->asset_url . '/jscripts/dvz_code_tags/prismjsCodeFormatter.js" data-options=\'' . \addcslashes($optionsJson, '\'') .'\' data-lang=\'' . \addcslashes($langJson, '\'') . '\'></script>' . PHP_EOL;

        return $bodyHtml;
    }

    public function getFormattedCode(array $match, ?int $placeholderCount = null, ?int $placeholderNo = null, ?int $cumulativeContentLength = null): ?string
    {
        $content = \htmlspecialchars_uni($match['content']);

        $attributes = [];
        $classes = [];

        $classes[] = 'block-code';
        $classes[] = 'line-numbers';
        $classes[] = 'language-none';

        $length = strlen($match['content']);

        $heavy = (
            $placeholderNo > \dvzCodeTags\getSettingValue('prismjs_code_formatter_heavy_count') ||
            $length > \dvzCodeTags\getSettingValue('prismjs_code_formatter_heavy_length')
        );

        if (!empty($match['language'])) {
            $language = \htmlspecialchars_uni($match['language']);
        } else {
            $language = 'none';
        }

        if ($heavy) {
            $attributes[] = 'data-deferred';
        }

        $attributes[] = 'class="' . implode(' ', $classes) . '"';

        $html = '<pre ' . implode(' ', $attributes) . '><code class="language-' . $language . '">' . $content . '</code></pre>';

        return $html;
    }

    public static function getSettingDefinitions(): ?array
    {
        return [
            'prismjs_code_formatter_resources_directory' => [
                'title'       => 'PrismJS code formatter: Resources directory',
                'description' => 'Enter path to the resources directory. <code>{ASSET_URL}</code> will be replaced with MyBB\'s static files path.',
                'optionscode' => 'text',
                'value'       => 'https://cdnjs.cloudflare.com/ajax/libs/prism/1.20.0/',
            ],
            'prismjs_code_formatter_components_directory' => [
                'title'       => 'PrismJS code formatter: Components directory',
                'description' => 'Enter path to the components directory. <code>{ASSET_URL}</code> will be replaced with MyBB\'s static files path.',
                'optionscode' => 'text',
                'value'       => 'https://cdnjs.cloudflare.com/ajax/libs/prism/1.20.0/components/',
            ],
            'prismjs_code_formatter_css_files' => [
                'title'       => 'PrismJS code formatter: CSS files',
                'description' => 'Enter stylesheet files to be included in separate lines. Optional SRI checksums can be added with preceding whitespace.',
                'optionscode' => 'textarea',
                'value'       => 'themes/prism.min.css sha256-cuvic28gVvjQIo3Q4hnRpQSNB0aMw3C+kjkR0i+hrWg=
plugins/line-numbers/prism-line-numbers.min.css sha256-Afz2ZJtXw+OuaPX10lZHY7fN1+FuTE/KdCs+j7WZTGc=
plugins/show-invisibles/prism-show-invisibles.min.css sha256-nOfkEPu/TzBvIxN8D/vW8weOUczzDbxRjgik/hA2hBI=
plugins/toolbar/prism-toolbar.min.css sha256-P45OhhEWm49G8sadt2n5rDaWLa3xZbDOQhJliuaojH0=',
            ],
            'prismjs_code_formatter_javascript_files' => [
                'title'       => 'PrismJS code formatter: JavaScript files',
                'description' => 'Enter JavaScript files to be included in separate lines. Optional SRI checksums can be added with preceding whitespace.',
                'optionscode' => 'textarea',
                'value'       =>'prism.min.js sha256-3teItwIfMuVB74Alnxw/y5HAZ2irOsCULFff3EgbtEs=
plugins/autoloader/prism-autoloader.min.js sha256-3S2PESHNt0YNL65z57WuHPHIv12fibpBDXepyCGHftw=
plugins/toolbar/prism-toolbar.min.js sha256-7I/IdbPM17QdjqRNwpVYj4iDGAQw7ZFHy9RSSU1yvLE=
plugins/show-language/prism-show-language.min.js sha256-0bmqeC8t2qjXEHcAn8OQJOLGzK0jE1wjepM2DvpS0xg=
plugins/line-numbers/prism-line-numbers.min.js sha256-hep5s8952MqR7Y79JYfCXZD6vQjVHs7sOu/ZGrs1OEQ=
plugins/show-invisibles/prism-show-invisibles.min.js sha256-1baFoczEXwdtWBiZ6gbu7W1kn6XfPgKY4LkQQYNHTkM=',
            ],
            'prismjs_code_formatter_heavy_count' => [
                'title'       => 'PrismJS code formatter: Heavy Count',
                'description' => 'Enter number of code snippets in message above which some features will be disabled to improve performance.',
                'optionscode' => 'numeric',
                'value'       => '25',
            ],
            'prismjs_code_formatter_heavy_length' => [
                'title'       => 'PrismJS code formatter: Heavy Length',
                'description' => 'Enter code length in number of characters above which some features will be disabled to improve performance.',
                'optionscode' => 'numeric',
                'value'       => '2500',
            ],
        ];
    }

    private function getDirectoryPaths(): array
    {
        global $mybb;

        static $directories;

        if (!$directories) {
            $directories = [
                'resources' => \dvzCodeTags\getSettingValue('prismjs_code_formatter_resources_directory'),
                'components' => \dvzCodeTags\getSettingValue('prismjs_code_formatter_components_directory'),
            ];

            foreach ($directories as $name => &$path) {
                $path = str_replace('{ASSET_URL}', $mybb->asset_url, $path);
            }
        }

        return $directories;
    }
}
