<?php

namespace Gregwar\Image\Utils;

class FileUtils
{
    /**
     * Checks that the file exists and is not a phar archive
     */
    public static function safeExists($file)
    {
        return file_exists($file) && !self::isPhar($file);
    }

    /**
     * Checks if the file is a phar archive
     */
    public static function isPhar($file)
    {
        return substr(strtolower(trim($file)), 0, 7) === 'phar://';
    }
}
