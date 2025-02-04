<?php
// Remove expired product from user's account
add_action('template_redirect', 'vpew_remove_expired_product_access');

function vpew_remove_expired_product_access() {
    if (!is_user_logged_in() || !is_product()) return;

    global $post;
    $user_id = get_current_user_id();
    $product_id = $post->ID;
    $product = wc_get_product($product_id);

    if (!$product) return; // Ensure product exists

    // Get expiration date for this user-product combination
    $expiration_date = get_user_meta($user_id, '_vpew_expiration_' . $product_id, true);

    // If expiration date exists and has passed, check removal setting
    if ($expiration_date && time() > $expiration_date) {
        $remove_expired = get_option('vpew_remove_expired', 'yes'); // Default: remove

        if ($remove_expired === 'yes') {
            // Remove from WooCommerce downloads (for downloadable products)
            if ($product->is_downloadable()) {
                global $wpdb;
                $deleted = $wpdb->delete(
                    "{$wpdb->prefix}woocommerce_downloadable_product_permissions",
                    array(
                        'user_id'    => $user_id,
                        'product_id' => $product_id,
                    ),
                    array('%d', '%d')
                );

                if (!$deleted) {
                    error_log("Failed to remove product $product_id from user $user_id's downloads.");
                }
            }

            // Remove expiration record so it does not trigger again
            delete_user_meta($user_id, '_vpew_expiration_' . $product_id);

            // Redirect to a meaningful page
            $redirect_url = wc_get_page_permalink('myaccount') ?: wc_get_page_permalink('shop');
            wp_redirect($redirect_url);
            exit;
        } else {
            // If removal is disabled, block access instead
            wp_die(__('Access to this product has expired. Please repurchase to regain access.', 'virtual-product-expiration'));
        }
    }
}
