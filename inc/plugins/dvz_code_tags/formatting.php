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
    foreach ($placeholders as $index => $match) {
        $replacement = \dvzCodeTags\Formatting\getFormattedOutput($match);

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

function getFormattedOutput(array $match): string
{
    global $mybb;

    // create/get formatter instance for specified type
    $codeFormatterInstance = \dvzCodeTags\getCodeFormatterInstance($match['type'], true);

    return $codeFormatterInstance->getFormattedCode($match);

    return $html;
}
