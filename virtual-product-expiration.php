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
 * Requires Plugins: woocommerce

 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

// Define Plugin Constants
define('VPEW_VERSION', '1.0.0');
define('VPEW_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('VPEW_PLUGIN_URL', plugin_dir_url(__FILE__));

// Activation Hook: Runs when the plugin is activated
register_activation_hook(__FILE__, 'vpew_activate');
function vpew_activate() {
    // Future database setup or initial settings can be added here
}

// Deactivation Hook: Runs when the plugin is deactivated
register_deactivation_hook(__FILE__, 'vpew_deactivate');
function vpew_deactivate() {
    // Cleanup tasks (if needed in future)
}

// Hook into WooCommerce order completion to set expiration date
add_action('woocommerce_order_status_completed', 'vpew_set_virtual_product_expiration', 10, 1);

function vpew_set_virtual_product_expiration($order_id) {
    $order = wc_get_order($order_id);
    $user_id = $order->get_user_id();

    if (!$user_id) return; // Ensure it's a registered user

    foreach ($order->get_items() as $item_id => $item) {
        $product = $item->get_product();

        if ($product && $product->is_virtual()) {
            $product_id = $product->get_id();
            $expiration_period = get_option('vpew_expiration_period', 365); // Default to 365 days
            $expiration_date = strtotime("+$expiration_period days", strtotime($order->get_date_completed()));
            
            // Save expiration date per user and per product
            update_user_meta($user_id, '_vpew_expiration_' . $product_id, $expiration_date);
        }
    }
}

// Restrict access to virtual products after expiration
add_action('template_redirect', 'vpew_restrict_expired_product_access');

function vpew_restrict_expired_product_access() {
    if (!is_user_logged_in() || !is_product()) return;

    global $post;
    $user_id = get_current_user_id();
    $product_id = $post->ID;

    // Get the expiration date for this user-product combination
    $expiration_date = get_user_meta($user_id, '_vpew_expiration_' . $product_id, true);

    // If the expiration date exists and has passed, block access
    if ($expiration_date && time() > $expiration_date) {
        wp_die(__('Access to this product has expired. Please repurchase to regain access.', 'virtual-product-expiration'));
    }
}

// Show expiration date in WooCommerce My Account orders
add_action('woocommerce_order_item_meta_end', 'vpew_show_expiration_in_my_account', 10, 3);

function vpew_show_expiration_in_my_account($item_id, $item, $order) {
    $user_id = $order->get_user_id();
    $product = $item->get_product();

    if ($product && $product->is_virtual()) {
        $product_id = $product->get_id();
        $expiration_date = get_user_meta($user_id, '_vpew_expiration_' . $product_id, true);

        if ($expiration_date) {
            echo '<p><strong>' . __('Access Expires:', 'virtual-product-expiration') . '</strong> ' . date('F j, Y', $expiration_date) . '</p>';
        }
    }
}

// Add a custom section in WooCommerce Settings → Products
add_filter('woocommerce_get_settings_products', 'vpew_add_expiration_settings', 10, 2);

function vpew_add_expiration_settings($settings, $current_section) {
    if ($current_section == 'vpew_expiration_settings') {
        $settings = array(
            array(
                'title' => __('Virtual Product Expiration Settings', 'virtual-product-expiration'),
                'type'  => 'title',
                'desc'  => __('Set the expiration time for virtual products after purchase.', 'virtual-product-expiration'),
                'id'    => 'vpew_expiration_settings_title'
            ),
            array(
                'title'    => __('Expiration Period (Days)', 'virtual-product-expiration'),
                'desc'     => __('Number of days before access expires.', 'virtual-product-expiration'),
                'id'       => 'vpew_expiration_period',
                'default'  => '365',
                'type'     => 'number',
                'css'      => 'min-width:300px;',
                'desc_tip' => true,
            ),
            array(
                'type' => 'sectionend',
                'id'   => 'vpew_expiration_settings_title'
            ),
        );
    }
    return $settings;
}

// Add the new section to WooCommerce Settings → Products
add_filter('woocommerce_get_sections_products', 'vpew_add_expiration_section');

function vpew_add_expiration_section($sections) {
    $sections['vpew_expiration_settings'] = __('Virtual Product Expiration', 'virtual-product-expiration');
    return $sections;
}

