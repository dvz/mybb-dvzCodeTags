<?php

namespace dvzCodeTags\Hooks;

function parse_message_start($message): string
{
    $parserInstance = null;

    if (isset($GLOBALS['parser']) && $GLOBALS['parser'] instanceof \postParser) {
        $parserInstance = $GLOBALS['parser'];
    }

    $message = str_replace([
        "\r",
        \dvzCodeTags\PARSER_EXCLUSIVE_CHAR,
    ], null, $message);

    if (\dvzCodeTags\getSettingValue('bypass_wordfilters') == 'nowhere') {
        if (!empty($parserInstance->options['filter_badwords'])) {
            $message = $parserInstance->parse_badwords($message);
        }
    }

    if (!empty($parserInstance->options['filter_cdata'])) {
        $message = $parserInstance->parse_cdata($message);
    }

    // iterate over matching functions separately to provide exclusive precedence when nested
    if (\dvzCodeTags\getSettingValue('parse_block_fenced_code')) {
        $matches = \dvzCodeTags\Parsing\getFencedCodeMatches($message);

        $message = \dvzCodeTags\Formatting\getMessageWithPlaceholders(
            $message,
            $matches,
            $GLOBALS['dvzCodeTagsPlaceholders']
        );
    }

    if (\dvzCodeTags\getSettingValue('parse_block_mycode_code')) {
        $matches = \dvzCodeTags\Parsing\getMycodeCodeMatches($message);

        $message = \dvzCodeTags\Formatting\getMessageWithPlaceholders(
            $message,
            $matches,
            $GLOBALS['dvzCodeTagsPlaceholders']
        );
    }

    if (\dvzCodeTags\getSettingValue('bypass_wordfilters') == 'block') {
        if (!empty($parserInstance->options['filter_badwords'])) {
            $message = $parserInstance->parse_badwords($message);
        }
    }

    if (\dvzCodeTags\getSettingValue('parse_inline_backticks_code')) {
        $matches = \dvzCodeTags\Parsing\getInlineCodeMatches($message);

        $message = \dvzCodeTags\Formatting\getMessageWithPlaceholders(
            $message,
            $matches,
            $GLOBALS['dvzCodeTagsPlaceholders']
        );
    }

    if (\dvzCodeTags\getSettingValue('bypass_wordfilters') == 'everywhere') {
        if (!empty($parserInstance->options['filter_badwords'])) {
            $message = $parserInstance->parse_badwords($message);
        }
    }

    return $message;
}

function parse_message_end(string $message): string
{
    $message = \dvzCodeTags\Formatting\getFormattedMessageFromPlaceholders(
        $message,
        $GLOBALS['dvzCodeTagsPlaceholders']
    );

    return $message;
}

function pre_output_page($content): string
{
    $instances = [
        \dvzCodeTags\getCodeFormatterInstance('block'),
        \dvzCodeTags\getCodeFormatterInstance('inline'),
    ];

    foreach ($instances as $instance) {
        if ($instance !== null) {
            $headEndHtml = $instance->getHeadEndHtml();

            if ($headEndHtml !== null) {
                $content = str_replace('</head>', $headEndHtml . "\n" . '</head>', $content);
            }

            $bodyEndHtml = $instance->getBodyEndHtml();

            if ($headEndHtml !== null) {
                $content = str_replace('</body>', $bodyEndHtml . "\n" . '</body>', $content);
            }
        }
    }

    return $content;
}
