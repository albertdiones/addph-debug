<?php
namespace addph\debug;

/**
 * log debug class
 *
 */
CLASS log EXTENDS debug {

    static $file;

    public static function file() {
        if (!isset(static::$file)) {
            static::$file = static::config()->root_dir."/debug.log.txt";
        }
        return static::$file;
    }

    public static function content_type() {
        return 'text/plain';
    }

    /**
     * echo only if IP matched
     *
     * @param string $arg the string to echo
     *
     */
    static function restricted_echo($arg) {
        $file = static::file();
        $divider = static::entry_divider();
        file_put_contents($file,$arg.$divider.@file_get_contents($file));
    }

    static function entry_divider() {
        return <<<EOT

======================================

EOT;

    }

}
