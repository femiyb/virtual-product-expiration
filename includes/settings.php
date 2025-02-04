<?php
// Add custom section in WooCommerce Settings → Products
add_filter('woocommerce_get_settings_products', 'vpew_add_expiration_settings', 10, 2);

function vpew_add_expiration_settings($settings, $current_section) {
    if ($current_section == 'vpew_expiration_settings') {
        $settings = array(
            array(
                'title' => __('Virtual Product Expiration Settings', 'virtual-product-expiration'),
                'type'  => 'title',
                'desc'  => __('Set the expiration behavior for virtual products.', 'virtual-product-expiration'),
                'id'    => 'vpew_expiration_settings_title'
            ),
            array(
                'title'    => __('Remove Expired Products', 'virtual-product-expiration'),
                'desc'     => __('Remove expired virtual products from the user\'s account instead of just blocking access.', 'virtual-product-expiration'),
                'id'       => 'vpew_remove_expired',
                'default'  => 'yes',
                'type'     => 'checkbox',
                'sanitize_callback' => 'vpew_sanitize_checkbox'
            ),
            array(
                'type' => 'sectionend',
                'id'   => 'vpew_expiration_settings_title'
            ),
        );
    }
    return $settings;
}

// Add new section to WooCommerce Settings → Products
add_filter('woocommerce_get_sections_products', 'vpew_add_expiration_section');

function vpew_add_expiration_section($sections) {
    $sections['vpew_expiration_settings'] = __('Virtual Product Expiration', 'virtual-product-expiration');
    return $sections;
}

// Sanitize checkbox input (ensures value is always 'yes' or 'no')
function vpew_sanitize_checkbox($value) {
    return ($value === 'yes') ? 'yes' : 'no';
}
