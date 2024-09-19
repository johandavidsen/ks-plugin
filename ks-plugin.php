<?php
/**
 * The KS Plugin
 *
 * This plugin contains custom functionality for the KS website and intranet. Primarily, it
 * contains custom taxonomies, post types and related functionality. This plugin supports:
 *
 * * multi-site installations
 * * ACF Pro if available
 * * Polylang if available
 *
 *
 * Plugin Name: KS Plugin
 * Plugin URI: https://fjakkarin.com/ks-plugin
 * Description: This plugin contains custom functionality for the KS website and intranet.
 * Version: v0.5.0
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Author: Jóhan H Dam-Davidsen
 * Author URI: https://fjakkarin.com
 * License: GPL2
 * Text Domain: KS-plugin
 * Domain Path: /languages
 */

namespace KSPlugin;

// Prevent direct access to the file
if (!\defined('ABSPATH')) {
    exit;
}

// Define plugin constants
\define('KS_PLUGIN_VERSION', 'v0.5.0');
\define('KS_DIR', \plugin_dir_path(__FILE__));
\define('KS_URL', \plugin_dir_url(__FILE__));

// Include necessary files
require_once(KS_DIR . 'inc/class-language.php');
require_once(KS_DIR . 'inc/class-department-taxonomy.php');

// Initialize the plugin
new Languages();
new DepartmentTaxonomy();
