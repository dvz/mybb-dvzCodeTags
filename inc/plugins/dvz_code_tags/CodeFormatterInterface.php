<?php

namespace dvzCodeTags\Formatting;

interface CodeFormatter
{
    public function getHeadEndHtml(): ?string;
    public function getBodyEndHtml(): ?string;
    public function getFormattedCode(array $match, ?int $placeholderCount = null): ?string;
    public function getSettingDefinitions(): ?array;
}
