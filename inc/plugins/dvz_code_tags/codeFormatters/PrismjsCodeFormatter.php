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
                'value'       => 'https://cdnjs.cloudflare.com/ajax/libs/prism/1.25.0/',
            ],
            'prismjs_code_formatter_components_directory' => [
                'title'       => 'PrismJS code formatter: Components directory',
                'description' => 'Enter path to the components directory. <code>{ASSET_URL}</code> will be replaced with MyBB\'s static files path.',
                'optionscode' => 'text',
                'value'       => 'https://cdnjs.cloudflare.com/ajax/libs/prism/1.25.0/components/',
            ],
            'prismjs_code_formatter_css_files' => [
                'title'       => 'PrismJS code formatter: CSS files',
                'description' => 'Enter stylesheet files to be included in separate lines. Optional SRI checksums can be added with preceding whitespace.',
                'optionscode' => 'textarea',
                'value'       => 'themes/prism.min.css sha512-tN7Ec6zAFaVSG3TpNAKtk4DOHNpSwKHxxrsiw4GHKESGPs5njn/0sMCUMl2svV4wo4BK/rCP7juYz+zx+l6oeQ==
plugins/line-numbers/prism-line-numbers.min.css sha512-cbQXwDFK7lj2Fqfkuxbo5iD1dSbLlJGXGpfTDqbggqjHJeyzx88I3rfwjS38WJag/ihH7lzuGlGHpDBymLirZQ==
plugins/show-invisibles/prism-show-invisibles.min.css sha512-y56hHawsGNNkestAKyhMKxX25fF3QjUkrUESd4qYTPlKqz1s890GRkp95U7vj3tS1Qr/NCYE3bbYOq9MzAXI/A==
plugins/toolbar/prism-toolbar.min.css sha512-ycl7dIZ0VJ5535/dzskAMTwOI6OoTNZ3PeD+tfckvYqMmAzaEwQfJHqJTSqcV2iQeJnp5XxnFy5jKotibstp7A==',
            ],
            'prismjs_code_formatter_javascript_files' => [
                'title'       => 'PrismJS code formatter: JavaScript files',
                'description' => 'Enter JavaScript files to be included in separate lines. Optional SRI checksums can be added with preceding whitespace.',
                'optionscode' => 'textarea',
                'value'       =>'prism.min.js sha512-hpZ5pDCF2bRCweL5WoA0/N1elet1KYL5mx3LP555Eg/0ZguaHawxNvEjF6O3rufAChs16HVNhEc6blF/rZoowQ==
plugins/autoloader/prism-autoloader.min.js sha512-sv0slik/5O0JIPdLBCR2A3XDg/1U3WuDEheZfI/DI5n8Yqc3h5kjrnr46FGBNiUAJF7rE4LHKwQ/SoSLRKAxEA==
plugins/toolbar/prism-toolbar.min.js sha512-YrvgEHAi5/3o2OT+/vh1z19oJXk/Kk0qdVKbjEFl9VRmcLTaWRYzVziZCvoGpJ2TrnV7rB8pnJcz1ioVJjgw2A==
plugins/show-language/prism-show-language.min.js sha512-teI3HjGzxHZz40l8V9ViL7ga18moIgswEgojE/Zl8jhAPhwkZI5/Y+RcIi8yMfA0TW0XHnfOpcmdm9+xj8atow==
plugins/line-numbers/prism-line-numbers.min.js sha512-dubtf8xMHSQlExGRQ5R7toxHLgSDZ0K7AunqPWHXmJQ8XyVIG19S1T95gBxlAeGOK02P4Da2RTnQz0Za0H0ebQ==
plugins/show-invisibles/prism-show-invisibles.min.js sha512-PVMnmOnCm6A2IHP8RMGclS5lEYaJyzpYkov5bfQs7MHYibBbh0JqE1/D3XQ9AkQCBzcsVmntztTnI7VOwOulXg==',
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
