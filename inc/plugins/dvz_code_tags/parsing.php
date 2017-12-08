<?php

namespace dvzCodeTags\Parsing;

function getInlineCodeMatches(string $message): array
{
    $regex = '/(`+)[ ]*(.+?)[ ]*(?<!`)\1(?!`)/u';

    preg_match_all($regex, $message, $regexMatchSets, PREG_SET_ORDER | PREG_OFFSET_CAPTURE);

    $matches = [];

    foreach ($regexMatchSets as $regexMatchSet) {
        $matches[] = [
            'offset' => $regexMatchSet[0][1],
            'full' => $regexMatchSet[0][0],
            'content' => $regexMatchSet[2][0],
            'type' => 'inline',
            'syntax' => 'backticks',
        ];
    }

    return $matches;
}

function getFencedCodeMatches(string $message): array
{
    $regex = '/(?:^|\r|\n|\r\n)(`{3,})([a-zA-Z0-9-]+)?(?:\r|\n|\r\n)(.+?)\1/su';

    preg_match_all($regex, $message, $regexMatchSets, PREG_SET_ORDER | PREG_OFFSET_CAPTURE);

    $matches = [];

    foreach ($regexMatchSets as $regexMatchSet) {
        $matches[] = [
            'offset' => $regexMatchSet[0][1],
            'full' => $regexMatchSet[0][0],
            'language' => $regexMatchSet[2][0],
            'content' => $regexMatchSet[3][0],
            'type' => 'block',
            'syntax' => 'fenced',
        ];
    }

    return $matches;
}

function getMycodeCodeMatches(string $message): array
{
    $regex = '/\[code(=([a-zA-Z0-9-]+))?\](?:\r|\n|\r\n)?(.*?)\[\/code\](\r\n?|\n?)/isu';

    preg_match_all($regex, $message, $regexMatchSets, PREG_SET_ORDER | PREG_OFFSET_CAPTURE);

    $matches = [];

    foreach ($regexMatchSets as $regexMatchSet) {
        $matches[] = [
            'offset' => $regexMatchSet[0][1],
            'full' => $regexMatchSet[0][0],
            'language' => $regexMatchSet[2][0],
            'content' => $regexMatchSet[3][0],
            'type' => 'block',
            'syntax' => 'mycode',
        ];
    }

    return $matches;
}
