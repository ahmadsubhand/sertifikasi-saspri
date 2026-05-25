<?php

namespace common\helpers;

class FileHelper
{
    public static function ensureDirectoryExists(string $directory): void
    {
        if (!is_dir($directory)) {
            mkdir($directory, 0777, true);
        }
    }

    public static function deleteFile(string $directory): void
    {
        if (is_file($directory)) {
            unlink($directory);
        }
    }

    public static $allowed_extensions = [
        'pdf',

        'jpg',
        'jpeg',
        'png',

        'doc',
        'docx',

        'xls',
        'xlsx',

        'csv',
    ];
}
