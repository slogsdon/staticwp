<?php

namespace StaticWP;

use \Exception;
use \WP_Query;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * StaticWPAdmin Plugin class
 *
 * Provides admin functionality.
 *
 * @package static-wp
 * @version 1.3.0
 * @author  slogsdon
 */
class StaticWPAdmin extends StaticWP
{
    protected $file = null;

    /**
     * Sets up necessary bits.
     *
     * @since 1.3.0
     *
     * @return void
     */
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
     * @return void
     */
    public function activate()
    {
        $muPluginDir = WP_CONTENT_DIR . '/mu-plugins';

        if (!is_dir($muPluginDir)) {
            wp_mkdir($muPluginDir);
        }

        if (!is_dir($this->destination)) {
            wp_mkdir_p($this->destination);
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

        // Can't use StaticWPView::page :(
        $notice = 'Thanks for installing StaticWP! Might we suggest <a href="'
                . admin_url('admin.php?page=staticwp-preload')
                . '">preloading your site</a>?';
        $this->addNotice($notice);
    }

    /**
     * Creates menu for StaticWP admin pages.
     *
     * @since 1.3.0
     *
     * @return void
     */
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

    /**
     * Cleans up after itself on deactivation.
     *
     * @since 1.1.0
     *
     * @return void
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
     * Displays admin notices.
     *
     * @since 1.3.0
     *
     * @return void
     */
    public static function displayNotices()
    {
        if ($notices = get_option('staticwp_deferred_admin_notices')) {
            foreach ($notices as $notice) {
                $message = $notice;
                $type = 'updated';

                if (is_array($notice)) {
                    $message = isset($notice['message']) ? $notice['message'] : '';
                    $type = isset($notice['type']) ? $notice['type'] : $type;
                }

                echo '<div class="' . $type . '"><p>' . $message . '</p></div>';
            }
            delete_option('staticwp_deferred_admin_notices');
        }
    }

    /**
     * Error handler to convert errors to exceptions to make it
     * easier to catch them.
     *
     * @param int    $num
     * @param string $mes
     * @param string $file
     * @param int    $line
     * @param array  $context
     *
     * @return bool
     */
    public function errorToException($num, $mes, $file = null, $line = null, $context = null)
    {
        throw new Exception($mes, $num);
    }

    /**
     * Handles form submission on StaticWP admin pages.
     *
     * @return void
     */
    public function handlePost()
    {
        if (!isset($_POST['staticwp-action'])) {
            return;
        }
        if (!check_admin_referer('staticwp')) {
            return;
        }

        switch ($_POST['action']) {
            case 'preload':
                $this->preload();
                break;
            default:
                break;
        }
    }

    /**
     * Displays info page.
     *
     * @since 1.3.0
     *
     * @return void
     */
    public function infoPage()
    {
        StaticWPView::page('admin/info');
    }

    /**
     * Hooks on to necessary actions/filters for the
     * administration end of the plugin.
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function initHooks()
    {
        register_activation_hook($this->file, array($this , 'activate'));
        register_deactivation_hook($this->file, array($this, 'deactivate'));
        register_uninstall_hook($this->file, array(__CLASS__, 'deactivate'));

        add_action('save_post', array($this, 'updateHtml'), 10, 2);
        add_action('admin_init', array($this, 'handlePost'));
        add_action('admin_init', array($this, 'update'));
        add_action('admin_menu', array($this, 'addMenu'));
        add_action('admin_notices', array(__CLASS__, 'displayNotices'));
    }

    /**
     * Displays preload page.
     *
     * @since 1.3.0
     *
     * @return void
     */
    public function preloadPage()
    {
        StaticWPView::page('admin/preload');
    }

    /**
     * Handles plugin updates.
     *
     * @since 1.3.0
     *
     * @return void
     */
    public function update()
    {
        $version = get_option('staticwp_version');
        if ($version != STATICWP_VERSION) {
            update_option('staticwp_version', STATICWP_VERSION);
        }
    }

    /**
     * Pushes a given notice to be displayed.
     *
     * @since 1.3.0
     *
     * @return void
     */
    protected function addNotice($notice)
    {
        $notices = get_option('staticwp_deferred_admin_notices', array());
        $notices[] = $notice;
        update_option('staticwp_deferred_admin_notices', $notices);
    }

    /**
     * Loops through posts to compile static HTML for each.
     *
     * @since 1.3.0
     *
     * @return void
     */
    protected function preload()
    {
        set_error_handler(array(__CLASS__, 'errorToException'), E_ALL);
        try {
            $args = array(
                'orderby'          => 'post_date',
                'order'            => 'DESC',
                'post_status'      => 'publish',
                'suppress_filters' => true,
            );
            $query = new WP_Query($args);

            if ($query->have_posts()) {
                foreach ($query->posts as $post) {
                    $this->updateHtml($post->ID);
                }
            }

            $this->addNotice(StaticWPView::notice('admin/preload-success'));
        } catch (Exception $e) {
            $this->addNotice(StaticWPView::notice('admin/preload-error', 'error'));
        }

        restore_error_handler();
        wp_reset_postdata();
        wp_safe_redirect(admin_url('admin.php?page=' . $this->plugin . '-preload'));
        exit();
    }

    /**
     * Recursively deletes a directory and its contents.
     *
     * @since 1.1.0
     * @param string $dir
     *
     * @return void
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
