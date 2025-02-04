<?php
// Set expiration date for virtual products upon order completion
add_action('woocommerce_order_status_completed', 'vpew_set_virtual_product_expiration', 10, 1);

function vpew_set_virtual_product_expiration($order_id) {
    $order = wc_get_order($order_id);
    $user_id = $order->get_user_id();

    if (!$user_id) return; // Ensure it's a registered user

    foreach ($order->get_items() as $item_id => $item) {
        $product = $item->get_product();

        if ($product && $product->is_virtual()) {
            $product_id = $product->get_id();

            // Get expiration period from product meta, fallback to global setting
            $expiration_period = get_post_meta($product_id, '_vpew_expiration_days', true);
            if (!$expiration_period || !is_numeric($expiration_period)) {
                $expiration_period = get_option('vpew_default_expiration_period', 365);
            }
            $expiration_period = intval($expiration_period); // Ensure it's an integer

            // Get order completion date
            $order_completed_date = $order->get_date_completed();
            if (!$order_completed_date) {
                continue; // Skip if order completion date is missing
            }

            // Calculate expiration date
            $expiration_date = strtotime("+$expiration_period days", strtotime($order_completed_date));

            // Ensure expiration date is valid before saving
            if ($expiration_date !== false) {
                update_user_meta($user_id, '_vpew_expiration_' . $product_id, $expiration_date);
            }
        }
    }
}
