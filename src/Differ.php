<?php

namespace Differ\Differ;

use Exception;

use function Differ\Differ\Formatters\plain;
use function Differ\Differ\Formatters\stylish;
use function Differ\Differ\parser;

function genDiff(string $firstFile, string $secondFile, string $format = 'stylish'): string
{
    try {
        filesIsExists([$firstFile, $secondFile]);
    } catch (Exception $e) {
        return $e->getMessage();
    }

    [$firstAsArray, $secondAsArray] = getFiles($firstFile, $secondFile);
    return formatted($format, getDiff($firstAsArray, $secondAsArray));
}

function getDiff(array $firstList, array $secondList): array
{
    $keys = getAllUniqueKeys($firstList, $secondList);
    $diff = array_map(function ($key) use ($firstList, $secondList) {
        if (is_array($firstList[$key] ?? null) && is_array($secondList[$key] ?? null)) {
            return [
                'key' => $key,
                'description' => 'parent',
                'children' => getDiff($firstList[$key], $secondList[$key]),
            ];
        }
        return parser($key, $firstList, $secondList);
    }, $keys);

    return array_values($diff);
}

function getAllUniqueKeys(array $firstList, array $secondList): array
{
    $keys = array_unique(
        array_merge(
            array_keys($firstList),
            array_keys($secondList)
        )
    );
    asort($keys, SORT_STRING);
    return $keys;
}

function formatted(string $format, $diff): string
{
    switch ($format) {
        case 'stylish':
            return trim(stylish($diff));
        case 'plain':
            return trim(plain($diff));
        case 'json':
            return json_encode($diff, JSON_PRETTY_PRINT);
        default:
            return trim(stylish($diff));
    }
}

/**
 * @throws Exception
 */
function filesIsExists(array $files): void
{
    foreach ($files as $file) {
        if (!file_exists($file)) {
            throw new Exception(sprintf("%s file not exists", $file));
        }
    }
}
