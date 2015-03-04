<?php
/*
Plugin Name: StaticWP
Description: Converts your blog into a static site.
Author: Shane Logsdon
Version: 1.0.0
Author URI: http://www.slogsdon.com/
*/

namespace StaticWP;

/**
 * StaticWP Plugin class
 *
 * Converts your blog into a static site.
 *
 * @package static-wp
 * @version 1.0.0
 * @author  slogsdon
 */
class StaticWP
{
    public function __construct()
    {
        if (is_admin()) {
            $this->initAdminHooks();
        } else {
            $this->initFrontendHooks();
        }
    }

    /**
     * Hooks on to necessary actions/filters for the
     * business end of the plugin.
     *
     * @return null
     */
    public function initFrontendHooks()
    {
        add_action('muplugins_loaded', array($this, 'load'), 0);
    }

    /**
     * Hooks on to necessary actions/filters for the
     * administration end of the plugin.
     *
     * @return null
     */
    public function initAdminHooks()
    {
        register_activation_hook(__FILE__, array($this , 'activate'));
        add_action('publish_post', array($this, 'updateHtml'), 10, 2);
    }

    /**
     * Upon initial activation, the plugin moves itself to
     * mu-plugins to take advantage of the muplugins_loaded
     * hook.
     *
     * @return null
     */
    public function load()
    {
        // We only care about GET requests at the moment
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            return;
        }

        $plugin = basename(__FILE__, '.php');

        $file = $_SERVER['REQUEST_URI'] . 'index.html';
        if (is_file(WP_PLUGIN_DIR . '/' . $plugin . '/_site' . $file)) {
            echo file_get_contents(WP_PLUGIN_DIR . '/' . $plugin . '/_site' . $file);
            exit();
        }
    }

    public function updateHtml($id, $post)
    {
        $plugin = basename(__FILE__, '.php');
        $permalink = get_permalink($id);
        $uri = substr($permalink, strlen(get_option('home')));
        $data = file_get_contents($permalink);
        $filename = WP_PLUGIN_DIR . '/' . $plugin . '/_site' . $uri . 'index.html';
        if (!is_dir(dirname($filename))) {
            mkdir(dirname($filename), 0775, true);
        }
        file_put_contents($filename, $data);
    }

    /**
     * Upon initial activation, the plugin creates a MU
     * version of itself to mu-plugins to take advantage
     * of the muplugins_loaded hook. It leaves support files
     * in their original location (plugins folder).
     *
     * @return null
     */
    public function activate()
    {
        $muPluginDir = WP_CONTENT_DIR . '/mu-plugins';
        $plugin = basename(__FILE__, '.php');
        $muPluginFile = $plugin . '/mu.php';

        if (!is_dir($muPluginDir)) {
            mkdir($muPluginDir, 0775);
        }

        if (!is_dir(WP_PLUGIN_DIR . '/' . $plugin . '/_site')) {
            mkdir(WP_PLUGIN_DIR . '/' . $plugin . '/_site', 0775);
        }

        $data = "<?php\n"
              . "/*\n"
              . "Plugin Name: StaticWP MU\n"
              . "Description: Converts your blog into a static site.\n"
              . "Author: Shane Logsdon\n"
              . "Version: 1.0.0\n"
              . "Author URI: http://www.slogsdon.com/\n"
              . "*/\n"
              . "\n"
              . "require_once '" . WP_PLUGIN_DIR . '/' . $plugin . '/' . $plugin . "-mu.php';\n";
        file_put_contents($muPluginDir . '/' . $plugin . '.php', $data);
    }
}
new StaticWP();
