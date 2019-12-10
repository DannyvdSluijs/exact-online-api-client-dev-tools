<?php declare(strict_types=1);

namespace DevTools;

class MetaDataHelper
{
    public static function convertUriToFilePath(string $uri): string
    {
        $subPath = str_replace(['/api/v1/{division}', '/api/v1/current'], '', $uri);
        return ucwords($subPath, '/');
    }
}