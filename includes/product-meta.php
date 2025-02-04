<?php
// Add custom expiration field to WooCommerce product settings (only for virtual products)
add_action('woocommerce_product_options_general_product_data', 'vpew_add_expiration_field');

function vpew_add_expiration_field() {
    global $product_object;

    // Get current expiration value if set
    $expiration_days = get_post_meta($product_object->get_id(), '_vpew_expiration_days', true);

    echo '<div class="options_group show_if_virtual">'; // Show only for virtual products

    woocommerce_wp_text_input(array(
        'id'          => '_vpew_expiration_days',
        'label'       => __('Access Expiration (Days)', 'virtual-product-expiration'),
        'description' => __('Set the number of days before this virtual product expires after purchase.', 'virtual-product-expiration'),
        'desc_tip'    => true,
        'type'        => 'number',
        'custom_attributes' => array(
            'min' => '1',
        ),
        'value'       => $expiration_days, // Pre-fill with existing value
    ));

    echo '</div>';
}

// Save the expiration period when the product is updated
add_action('woocommerce_process_product_meta', 'vpew_save_expiration_field');

function vpew_save_expiration_field($product_id) {
    if (isset($_POST['_vpew_expiration_days']) && is_numeric($_POST['_vpew_expiration_days'])) {
        update_post_meta($product_id, '_vpew_expiration_days', absint($_POST['_vpew_expiration_days']));
    } else {
        delete_post_meta($product_id, '_vpew_expiration_days'); // Remove if invalid or empty
    }
}
