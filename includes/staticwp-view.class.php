<?php

namespace StaticWP;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * StaticWPView class
 *
 * Provides helpers for loading template files
 *
 * @package static-wp
 * @version 1.2.0
 * @author  slogsdon
 */
class StaticWPView
{
    public static function notice($slug)
    {
        ob_start();
        self::template($slug, 'notice');
        return ob_get_flush();
    }

    public static function page($slug)
    {
        self::template($slug, 'page');
    }

    protected static function template($slug, $type)
    {
        include plugin_dir_path(__FILE__)
            . '../templates/' . $slug . '.' . $type . '.php';
    }
}
