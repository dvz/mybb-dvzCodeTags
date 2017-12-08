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

            if (isset($data[1])) {
                $additionalAttributes = ' integrity="' . $data[1] . '" crossorigin="anonymous"';
            } else {
                $additionalAttributes = null;
            }

            $optionsJson = json_encode([
                'components_directory' => \htmlspecialchars_uni($directories['components']),
            ]);

            $langJson = json_encode([
                'dvz_code_tags_select_all' => $lang->dvz_code_tags_select_all,
            ]);

            $bodyHtml .= '<script src="' . $directories['resources'] . $data[0] . '"' . $additionalAttributes . '></script>' . PHP_EOL;
        }

        $bodyHtml .= '<script src="' . $mybb->asset_url . '/jscripts/dvz_code_tags/prismjsCodeFormatter.js" data-options=\'' . \addcslashes($optionsJson, '\'') .'\' data-lang=\'' . \addcslashes($langJson, '\'') . '\'></script>' . PHP_EOL;

        return $bodyHtml;
    }

    public function getFormattedCode(array $match): ?string
    {
        $content = \htmlspecialchars_uni($match['content']);

        if (!empty($match['language'])) {
            $language = \htmlspecialchars_uni($match['language']);
        } else {
            $language = 'none';
        }

        $html = '<pre class="block-code line-numbers"><code class="language-' . $language . '">' . $content . '</code></pre>';

        return $html;
    }

    public function getSettingDefinitions(): ?array
    {
        return [
            'prismjs_code_formatter_resources_directory' => [
                'title'       => 'PrismJS code formatter: Resources directory',
                'description' => 'Enter path to the resources directory. <code>{ASSET_URL}</code> will be replaced with MyBB\'s static files path.',
                'optionscode' => 'text',
                'value'       => 'https://cdnjs.cloudflare.com/ajax/libs/prism/1.9.0/',
            ],
            'prismjs_code_formatter_components_directory' => [
                'title'       => 'PrismJS code formatter: Components directory',
                'description' => 'Enter path to the components directory. <code>{ASSET_URL}</code> will be replaced with MyBB\'s static files path.',
                'optionscode' => 'text',
                'value'       => 'https://cdnjs.cloudflare.com/ajax/libs/prism/1.9.0/components/',
            ],
            'prismjs_code_formatter_css_files' => [
                'title'       => 'PrismJS code formatter: CSS files',
                'description' => 'Enter stylesheet files to be included in separate lines. Optional SRI checksums can be added with preceding whitespace.',
                'optionscode' => 'textarea',
                'value'       => 'themes/prism.min.css sha256-DJJDQyzflMYiUb2jX3gilm9m3gAkHPC4WLv9BAk4rTE=
plugins/line-numbers/prism-line-numbers.min.css sha256-SA3HrKLu4tldegZQVdUx8Cwwo2hQFIu7iW6UOo6OdS4=
plugins/show-invisibles/prism-show-invisibles.min.css sha256-mhLIsMVR80BiRz+mG8IKgmGIguoQuTYxtPgUdmx3Jrw=
plugins/toolbar/prism-toolbar.min.css sha256-oqIQBM3BkdYsUyIdtwZql/C/XN5mGcz3LERZCJU/C0I=',
            ],
            'prismjs_code_formatter_javascript_files' => [
                'title'       => 'PrismJS code formatter: JavaScript files',
                'description' => 'Enter JavaScript files to be included in separate lines. Optional SRI checksums can be added with preceding whitespace.',
                'optionscode' => 'textarea',
                'value'       =>'prism.min.js sha256-hK55yYjZp3vXqUhowkWYVaoavcWSYRR6fE8sgHqyt5A=
plugins/autoloader/prism-autoloader.min.js sha256-/eFVLWtwHKExKyYUwicIThi9orKW0nxCqtU53BfLFpg=
plugins/toolbar/prism-toolbar.min.js sha256-QQEEvcdk8r1EU6emWNFc2AFPqgHTE5KBZ4jHkJX65i0=
plugins/show-language/prism-show-language.min.js sha256-3aaTGSelI31rfM1asKANKMk/8bxNAL8/WJgBx7lvRGI=
plugins/line-numbers/prism-line-numbers.min.js sha256-JfF9MVfGdRUxzT4pecjOZq6B+F5EylLQLwcQNg+6+Qk=
plugins/show-invisibles/prism-show-invisibles.min.js sha256-V62xAwTNFNOCJXJEuhm/kMsRDYrWnnULpww5h+y1j0s=',
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
