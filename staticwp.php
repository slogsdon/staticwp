<?php
/*
Plugin Name: StaticWP
Description: Converts your blog into a static site.
Author: Shane Logsdon
Version: 1.5.0
Author URI: http://www.slogsdon.com/
License: MIT
*/

namespace StaticWP;

if (!defined('ABSPATH')) {
    exit;
}

if (!defined('STATICWP_VERSION')) {
    define('STATICWP_VERSION', '1.5.0');
}

// Support
require_once 'includes/staticwp-view.class.php';

// Do the businesss
require_once 'includes/staticwp.class.php';
require_once 'includes/staticwp-admin.class.php';

$plugin = basename(__FILE__, '.php');
if (is_admin()) {
    new Admin($plugin, __FILE__);
} else {
    new StaticWP($plugin);
}
