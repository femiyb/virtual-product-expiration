<?php
/**
 * Plugin Name: Virtual Product Expiration Date for WooCommerce
 * Plugin URI:  https://github.com/yourusername/virtual-product-expiration
 * Description: Automatically sets an expiration date for WooCommerce virtual products after purchase and restricts access when expired.
 * Version:     1.0.0
 * Author:      Your Name
 * Author URI:  https://yourwebsite.com/
 * License:     GPL-2.0+
 * Text Domain: virtual-product-expiration
 * Domain Path: /languages
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

// Define Plugin Constants
define('VPEW_VERSION', '1.0.0');
define('VPEW_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('VPEW_PLUGIN_URL', plugin_dir_url(__FILE__));

// Include Plugin Files (Ensure Files Exist Before Including)
$includes = [
    'includes/settings.php',
    'includes/product-meta.php',
    'includes/expiration-handler.php',
    'includes/remove-access.php'
];

foreach ($includes as $file) {
    if (file_exists(VPEW_PLUGIN_DIR . $file)) {
        require_once VPEW_PLUGIN_DIR . $file;
    }
}

// Activation Hook
register_activation_hook(__FILE__, 'vpew_activate');
function vpew_activate() {
    // Future database setup or initial settings can be added here
    // Example: add_option('vpew_some_setting', 'default_value');
}

// Deactivation Hook
register_deactivation_hook(__FILE__, 'vpew_deactivate');
function vpew_deactivate() {
    // Cleanup tasks (if needed in future)
    // Example: delete_option('vpew_some_setting');
}
