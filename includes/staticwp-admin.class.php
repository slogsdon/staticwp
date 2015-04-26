<?php

namespace StaticWP;

use \Exception;
use \WP_Query;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Admin Plugin class
 *
 * Provides admin functionality.
 *
 * @package staticwp
 * @version 1.4.2
 * @author  slogsdon
 */
class Admin extends StaticWP
{
    const DEFAULT_SUB_PAGE = 'info';
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
            wp_mkdir_p($muPluginDir);
        }

        if (!is_dir($this->destination)) {
            wp_mkdir_p($this->destination);
        }

        $data = "<?php\n"
              . "/*\n"
              . "Plugin Name: StaticWP MU\n"
              . "Description: Converts your blog into a static site. This part of StaticWP"
                          . " allows StaticWP to bypass as much Wordpress as possible.\n"
              . "Author: Shane Logsdon\n"
              . 'Version: ' . STATICWP_VERSION . "\n"
              . "Author URI: http://www.slogsdon.com/\n"
              . "License: MIT\n"
              . "*/\n"
              . "\n"
              . "require_once '" . plugin_dir_path($this->file) . $this->plugin . ".php';\n";
        file_put_contents($muPluginDir . '/' . $this->plugin . '-mu.php', $data);

        // Can't use View::page :(
        $notice = 'Thanks for installing StaticWP! Might we suggest <a href="'
                . self::url('preload')
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
        add_submenu_page(
            'tools.php',
            __('StaticWP', $this->plugin),
            __('StaticWP', $this->plugin),
            'manage_options',
            $this->plugin,
            array($this, 'viewPage')
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

        delete_option($this->plugin . 'version');
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
    public static function errorToException($num, $mes, $file = null, $line = null, $context = null)
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

        switch ($_POST['staticwp-action']) {
            case 'preload':
                set_error_handler(array(__CLASS__, 'errorToException'), E_ALL);
                try {
                    $types = apply_filters('staticwp_preload_post_types', array('post', 'page'));
                    foreach ($types as $type) {
                      $this->preload($type);
                    }
                    $this->addNotice(View::notice('admin/preload-success'));
                } catch (Exception $e) {
                    $this->addNotice(View::notice('admin/preload-error', 'error'));
                }

                restore_error_handler();
                wp_reset_postdata();
                wp_safe_redirect(self::url('preload'));
                exit();
                break;
            default:
                break;
        }
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

        add_action('save_post', array($this, 'updateHtml'), 10, 2);
        add_action('admin_init', array($this, 'handlePost'));
        add_action('admin_init', array($this, 'update'));
        add_action('admin_menu', array($this, 'addMenu'));
        add_action('admin_notices', array(__CLASS__, 'displayNotices'));
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
        $version = get_option($this->plugin . 'version');
        if ($version != STATICWP_VERSION) {
            update_option($this->plugin . 'version', STATICWP_VERSION);
        }
    }

    /**
     * Creates an admin_url to a StaticWP subpage.
     *
     * @since 1.4.2
     *
     * @param string $subpage
     *
     * @return string
     */
    public static function url($subpage)
    {
        return admin_url('tools.php?page=staticwp'
          . (!empty($subpage) ? '&sub=' . $subpage : ''));
    }

    /**
     * Displays a page.
     *
     * @since 1.4.2
     *
     * @return void
     */
    public function viewPage()
    {
        $page = isset($_GET['sub']) ? (string)$_GET['sub'] : self::DEFAULT_SUB_PAGE;
        View::page('admin/' . $page);
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
    protected function preload($post_type = 'post')
    {
        $args = array(
            'fields'           => 'ids',
            'orderby'          => 'post_date',
            'order'            => 'DESC',
            'post_status'      => 'publish',
            'post_type'        => $post_type,
            'showposts'        => -1,
            'suppress_filters' => true,
        );
        $query = new WP_Query($args);

        if ($query->have_posts()) {
            $posts = apply_filters('staticwp_preload_' . $post_type . '_posts', $query->posts);
            foreach ($posts as $post_id) {
                $this->updateHtml($post_id);
            }
        }
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
