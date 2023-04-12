<?php

/**
 * @wordpress-plugin
 * Plugin Name:       BodyGraphChart
 * Plugin URI:        https://bodygraphchart.com/
 * Description:       The Body Graph Chart for your WordPress site.
 * Version:           1.0.0
 * Author:            BodyGraphChart
 * Author URI:        https://bodygraphchart.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       bgc
 */

if (!defined('WPINC')) {
    die;
}

define('BGC_PLUGIN_DIR', trailingslashit(dirname(__FILE__)));
define('BGC_PLUGIN_URL', plugin_dir_url(__FILE__));

include_once BGC_PLUGIN_DIR . 'includes/frontend.php';

if (is_admin()) {
    include_once BGC_PLUGIN_DIR . 'includes/backend.php';
}
