<?php

namespace dvzCodeTags\Formatting;

class DefaultInlineCodeFormatter implements InlineCodeFormatter
{
    public function getHeadEndHtml(): ?string
    {
        return null;
    }

    public function getBodyEndHtml(): ?string
    {
        return null;
    }

    public function getFormattedCode(array $match, ?int $placeholderCount = null): ?string
    {
        $html = '<code class="inline-code">' . \htmlspecialchars_uni($match['content']) . '</code>';

        return $html;
    }

    public function getSettingDefinitions(): ?array
    {
        return [];
    }
}
