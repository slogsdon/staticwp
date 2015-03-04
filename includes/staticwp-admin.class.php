<?php

namespace StaticWP;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * StaticWPAdmin Plugin class
 *
 * Provides admin functionality.
 *
 * @package static-wp
 * @version 1.2.0
 * @author  slogsdon
 */
class StaticWPAdmin extends StaticWP
{
    protected $file = null;

    public function __construct($plugin, $file = __FILE__)
    {
        $this->file = $file;
        parent::__construct($plugin);
        $this->initHooks();
    }
    /**
     * Upon initial activation, the plugin creates a MU
     * version of itself to mu-plugins to take advantage
     * of the muplugins_loaded hook. It leaves support files
     * in their original location (plugins folder).
     *
     * @since 1.0.0
     *
     * @return null
     */
    public function activate()
    {
        $muPluginDir = WP_CONTENT_DIR . '/mu-plugins';
        $muPluginFile = $this->plugin . '/mu.php';

        if (!is_dir($muPluginDir)) {
            mkdir($muPluginDir, 0775);
        }

        if (!is_dir($this->destination)) {
            mkdir($this->destination, 0775, true);
        }

        $data = "<?php\n"
              . "/*\n"
              . "Plugin Name: StaticWP MU\n"
              . "Description: Converts your blog into a static site.\n"
              . "Author: Shane Logsdon\n"
              . 'Version: ' . STATICWP_VERSION . "\n"
              . "Author URI: http://www.slogsdon.com/\n"
              . "License: MIT\n"
              . "*/\n"
              . "\n"
              . "require_once '" . plugin_dir_path($this->file) . $this->plugin . ".php';\n";
        file_put_contents($muPluginDir . '/' . $this->plugin . '-mu.php', $data);

        $notice = 'Thanks for installing StaticWP! Might we suggest <a href="'
                . admin_url('admin.php?page=staticwp-preload')
                . '">preloading your site</a>?';
        $this->addNotice($notice);
    }

    /**
     * Hooks on to necessary actions/filters for the
     * administration end of the plugin.
     *
     * @since 1.0.0
     *
     * @return null
     */
    public function initHooks()
    {
        register_activation_hook($this->file, array($this , 'activate'));
        register_deactivation_hook($this->file, array($this, 'deactivate'));
        register_uninstall_hook($this->file, array(__CLASS__, 'deactivate'));

        add_action('publish_post', array($this, 'updateHtml'), 10, 2);
        add_action('admin_menu', array($this, 'addMenu'));
        add_action('admin_notices', array(__CLASS__, 'displayNotices'));
    }

    public function addMenu()
    {
        add_menu_page(
            __('StaticWP', $this->plugin),
            __('StaticWP', $this->plugin),
            'manage_options',
            $this->plugin,
            array($this, 'InfoPage')
        );
        add_submenu_page(
            $this->plugin,
            __('StaticWP Preload', $this->plugin),
            __('Preload', $this->plugin),
            'manage_options',
            $this->plugin . '-preload',
            array($this, 'PreloadPage')
        );
    }

    public function infoPage()
    {
        StaticWPView::page('admin/preload');
    }

    public function preloadPage()
    {
        StaticWPView::page('admin/preload');
    }

    public function init()
    {
        $version = get_option('staticwp_version');
        if ($version != STATICWP_VERSION) {
            update_option('staticwp_version', $current_version);
        }
    }

    protected function addNotice($notice)
    {
        $notices = get_option('staticwp_deferred_admin_notices', array());
        $notices[] = $notice;
        update_option('staticwp_deferred_admin_notices', $notices);
    }

    public static function displayNotices()
    {
        if ($notices = get_option('staticwp_deferred_admin_notices')) {
            foreach ($notices as $notice) {
                echo '<div class="updated"><p>' . $notice . '</p></div>';
            }
            delete_option('staticwp_deferred_admin_notices');
        }
    }

    /**
     * Cleans up after itself on deactivation.
     *
     * @since 1.1.0
     *
     * @return null
     */
    public function deactivate()
    {
        $muPluginFile = WP_CONTENT_DIR . '/mu-plugins/' . $this->plugin . '-mu.php';

        if (is_file($muPluginFile)) {
            unlink($muPluginFile);
        }

        if (is_dir($this->destination)) {
            $this->rrmdir(dirname($this->destination));
        }

        delete_option('staticwp_version');
        delete_option('staticwp_deferred_admin_notices');
    }

    /**
     * Recursively deletes a directory and its contents.
     *
     * @since 1.1.0
     * @param string $dir
     *
     * @return null
     */
    protected function rrmdir($dir)
    {
        if (is_dir($dir)) {
            $objects = scandir($dir);
            foreach ($objects as $object) {
                if ($object != "." && $object != "..") {
                    if (filetype($dir."/".$object) == "dir") {
                        $this->rrmdir($dir."/".$object);
                    } else {
                        unlink($dir."/".$object);
                    }
                }
            }
            reset($objects);
            rmdir($dir);
        }
    }
}
