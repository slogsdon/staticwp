<?php
/*
Plugin Name: StaticWP
Description: Converts your blog into a static site.
Author: Shane Logsdon
Version: 1.2.0
Author URI: http://www.slogsdon.com/
License: MIT
*/

namespace StaticWP;

/**
 * StaticWP Plugin class
 *
 * Converts your blog into a static site.
 *
 * @package static-wp
 * @version 1.2.0
 * @author  slogsdon
 */
class StaticWP
{
    const VERSION          = '1.2.0';
    protected $destination = null;
    protected $plugin      = null;

    public function __construct()
    {
        $this->plugin = basename(__FILE__, '.php');
        $this->destination = $this->resolveDestination();

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
     * @since 1.0.0
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
     * @since 1.0.0
     *
     * @return null
     */
    public function initAdminHooks()
    {
        register_activation_hook(__FILE__, array($this , 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
        register_uninstall_hook(__FILE__, array(__CLASS__, 'deactivate'));
        add_action('publish_post', array($this, 'updateHtml'), 10, 2);
    }

    /**
     * Upon initial activation, the plugin moves itself to
     * mu-plugins to take advantage of the muplugins_loaded
     * hook.
     *
     * @since 1.0.0
     *
     * @return null
     */
    public function load()
    {
        // We only care about GET requests at the moment
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            return;
        }

        $file = $_SERVER['REQUEST_URI'] . 'index.html';
        if (is_file($this->destination . $file)) {
            echo file_get_contents($this->destination . $file);
            exit();
        }
    }

    /**
     * Updates the static HTML for a post.
     *
     * @since 1.0.0
     * @param int           $id
     * @param \WP_Post|null $post
     *
     * @return null
     */
    public function updateHtml($id, $post = null)
    {
        $permalink = get_permalink($id);
        $uri = substr($permalink, strlen(get_option('home')));
        $data = file_get_contents($permalink);
        $filename = $this->destination . $uri . 'index.html';
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
              . 'Version: ' . self::VERSION . "\n"
              . "Author URI: http://www.slogsdon.com/\n"
              . "License: MIT\n"
              . "*/\n"
              . "\n"
              . "require_once '" . plugin_dir_path(__FILE__) . $this->plugin . ".php';\n";
        file_put_contents($muPluginDir . '/' . $this->plugin . '-mu.php', $data);
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

    /**
     * Recursively deletes a directory and its contents.
     *
     * @since 1.2.0
     *
     * @return string
     */
    protected function resolveDestination()
    {
        $dir = '';
        $uploads = wp_upload_dir();

        if (isset($uploads['basedir'])) {
            $dir = $uploads['basedir'] . '/' . $this->plugin . '/_site';
        } else {
            $dir = WP_CONTENT_DIR . '/uploads/' . $this->plugin . '/_site';
        }

        return $dir;
    }
}
new StaticWP();
