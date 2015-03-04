<?php

namespace StaticWP;

if (!defined('ABSPATH')) {
    exit;
}

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
    protected $destination = null;
    protected $plugin      = null;

    public function __construct($plugin)
    {
        $this->plugin = $plugin;
        $this->destination = $this->resolveDestination();
        $this->initHooks();
    }

    /**
     * Hooks on to necessary actions/filters for the
     * business end of the plugin.
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function initHooks()
    {
        add_action('muplugins_loaded', array($this, 'load'), 0);
    }

    /**
     * Presents a compiled static HTML file when present.
     *
     * @since 1.0.0
     *
     * @return void
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
     * @return void
     */
    public function updateHtml($id, $post = null)
    {
        $permalink = get_permalink($id);
        $uri = substr($permalink, strlen(get_option('home')));
        $data = file_get_contents($permalink);
        $filename = $this->destination . $uri . 'index.html';
        $dir = $this->destination . $uri;

        if (!is_dir($dir)) {
            wp_mkdir_p($dir);
        }
        file_put_contents($filename, $data);
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
