<?php

/**
 * Keeps helper functions for all tests.
 *
 * @author Nikolay Traykov
 */
class Utils
{
    /**
     * Removes all files in a directory.
     * 
     * @param string $dir The path of the directory
     * @return boolean True if all files are deleted, false otherwise
     */
    public static function cleanDir($dir)
    {
        $files = scandir($dir);        
        foreach ($files as $file) {
            $currPath = "$dir/$file";
            if (is_dir($currPath)) {
                continue;
            }

            if (!unlink($currPath)) {
                return false;
            }
        }

        return true;
    }
}