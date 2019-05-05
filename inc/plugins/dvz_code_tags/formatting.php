<?php

namespace dvzCodeTags\Formatting;

function getMessageWithPlaceholders(string $message, array $matches, array &$placeholders = []): string
{
    foreach ($matches as &$match) {
        $placeholderId = count($placeholders);
        $placeholders[] = $match;

        $match['replacement'] = '{' . \dvzCodeTags\PARSER_EXCLUSIVE_CHAR . 'DVZ_CT#' . $placeholderId . '}';
    }

    $message = \dvzCodeTags\Formatting\replaceMatchesInMessage($message, $matches);

    return $message;
}

function getFormattedMessageFromPlaceholders(string $message, array $placeholders): string
{
    $placeholderCount = count($placeholders);

    foreach ($placeholders as $index => $match) {
        $replacement = \dvzCodeTags\Formatting\getFormattedOutput($match, $placeholderCount);

        $message = str_replace('{' . \dvzCodeTags\PARSER_EXCLUSIVE_CHAR . 'DVZ_CT#' . $index . '}', $replacement, $message);
    }

    return $message;
}

function replaceMatchesInMessage(string $message, array $matches): string
{
    $correction = 0;

    foreach ($matches as $match) {
        $start = $match['offset'] + $correction;

        $length = strlen($match['full']);

        $message = substr_replace($message, $match['replacement'], $start, $length);

        $correction += strlen($match['replacement']) - $length;
    }

    return $message;
}

function getFormattedOutput(array $match, ?int $placeholderCount = null): string
{
    // create/get formatter instance for specified type
    $codeFormatterInstance = \dvzCodeTags\getCodeFormatterInstance($match['type'], true);

    $html = $codeFormatterInstance->getFormattedCode($match, $placeholderCount);

    return $html;
}
