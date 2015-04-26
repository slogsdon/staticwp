<?php

namespace StaticWP;

use \Exception;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * StaticWP Plugin class
 *
 * Converts your blog into a static site.
 *
 * @package staticwp
 * @version 1.4.2
 * @author  slogsdon
 */
class StaticWP
{
    protected $destination = null;
    protected $plugin      = null;

    /**
     * Sets up necessary bits.
     *
     * @since 1.3.0
     *
     * @return void
     */
    public function __construct($plugin)
    {
        $this->plugin = $plugin;
        $this->destination = $this->resolveDestination();
        $this->initHooks();
    }

    /**
     * Updates static HTML after an approved comment is added.
     *
     * @param int $id
     * @param int @status
     *
     * @return void
     */
    public function addComment($id, $status)
    {
        if ($status == 0) {
            return;
        }

        $comment = get_comment($id);
        $this->updateHtml($comment->comment_post_ID);
        wp_safe_redirect(get_permalink($comment->comment_post_ID));
        exit();
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
        add_action('comment_post', array($this, 'addComment'), 10, 2);
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
            $contents = file_get_contents($this->destination . $file);
            do_action('staticwp_pre_cache_hit', $_SERVER['REQUEST_URI']);
            echo apply_filters('staticwp_cache_hit_contents', $contents);
            do_action('staticwp_post_cache_hit', $_SERVER['REQUEST_URI']);
            exit();
        } else {
            do_action('staticwp_cache_miss', $_SERVER['REQUEST_URI']);
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
        if ($post != null && $post->post_status != 'publish') {
            return;
        }

        $permalink = get_permalink($id);
        $uri = substr($permalink, strlen(get_option('home')));
        $filename = $this->destination . $uri . 'index.html';
        $dir = $this->destination . $uri;

        if (!is_dir($dir)) {
            wp_mkdir_p($dir);
        }

        if (is_file($filename)) {
            unlink($filename);
        }

        $curl = curl_init($permalink);

        curl_setopt($curl, CURLOPT_HEADER,         false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        $data = null;
        if (($data = curl_exec($curl)) === false) {
            throw new Exception(sprintf('Curl error: %s', curl_error($curl)));
        }

        curl_close($curl);
        do_action('staticwp_pre_cache_update', $id);
        file_put_contents($filename, apply_filters('staticwp_cache_update_contents', $data, $id));
        do_action('staticwp_post_cache_update', $id);
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

        return apply_filters('staticwp_cache_destination', $dir);
    }
}
