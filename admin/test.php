<?php
function can_use_coupon($coupon, $cart_items, $user_info, $user_coupon_usage) {
    // Check if the coupon is expired
    $current_date = new DateTime();
    $expiry_date = new DateTime($coupon['expiry_date']);
    if ($current_date > $expiry_date) {
        return false; // Coupon is expired
    }

    // Check if the user's email is restricted
    if (in_array($user_info['user_email'], $coupon['restricted_emails'])) {
        return false; // User's email is restricted from using the coupon
    }

    // Check if the user has already used the coupon (individual use)
    if ($coupon['is_individual_use'] == 1) {
        if (isset($user_coupon_usage[$user_info['user_id']][$coupon['name_code']]) && 
            $user_coupon_usage[$user_info['user_id']][$coupon['name_code']] >= 1) {
            return false; // User has already used this coupon once
        }
    }


    function apply_coupon_filter($coupon_filter, $cart_items) {
        $product_ids_in_cart = array_column($cart_items, 'item_id'); // Extract all product IDs from cart
        $category_ids_in_cart = get_cart_categories($cart_items); // A function to extract all category IDs from cart
    
        // Loop through the coupon filter rules
        foreach ($coupon_filter as $filter) {
            if ($filter['based_on'] == 'product_id') {
                // Include or exclude based on product IDs
                if ($filter['include_exclude'] == 'include') {
                    // Check if at least one product ID in the cart is in the allowed product IDs
                    if (empty(array_intersect($filter['product_ids'], $product_ids_in_cart))) {
                        return false; // No matching product IDs found, coupon can't be applied
                    }
                } elseif ($filter['include_exclude'] == 'exclude') {
                    // Check if any excluded product IDs are in the cart
                    if (!empty(array_intersect($filter['product_ids'], $product_ids_in_cart))) {
                        return false; // Found excluded product IDs, coupon can't be applied
                    }
                }
            } elseif ($filter['based_on'] == 'category_id') {
                // Include or exclude based on category IDs
                if ($filter['include_exclude'] == 'include') {
                    // Check if at least one category ID in the cart is in the allowed category IDs
                    if (empty(array_intersect($filter['category_ids'], $category_ids_in_cart))) {
                        return false; // No matching categories found, coupon can't be applied
                    }
                } elseif ($filter['include_exclude'] == 'exclude') {
                    // Check if any excluded category IDs are in the cart
                    if (!empty(array_intersect($filter['category_ids'], $category_ids_in_cart))) {
                        return false; // Found excluded categories, coupon can't be applied
                    }
                }
            }
        }
    
        // If none of the rules invalidated the coupon, return true
        return true;
    }
    
    // Function to get category IDs for each product in the cart
    function get_cart_categories($cart_items) {
        $category_ids = [];
        foreach ($cart_items as $item) {
            $product_id = $item['item_id'];
            $categories = get_the_terms($product_id, 'product_cat'); // Get categories of the product
            if ($categories) {
                foreach ($categories as $category) {
                    $category_ids[] = $category->term_id;
                }
            }
        }
        return $category_ids;
    }

    
    // Check product/category restrictions
    // if (!empty($coupon['coupon_filter'])) {
    //     foreach ($coupon['coupon_filter'] as $filter) {
    //         if ($filter['based_on'] == 'product_id') {
    //             $product_ids_in_cart = array_column($cart_items, 'item_id');
    //             if ($filter['include_exclude'] == 'include') {
    //                 // Check if cart has at least one product that the coupon can be applied to
    //                 if (empty(array_intersect($filter['product_ids'], $product_ids_in_cart))) {
    //                     return false; // No applicable products in the cart
    //                 }
    //             } elseif ($filter['include_exclude'] == 'exclude') {
    //                 // Check if none of the excluded products are in the cart
    //                 if (!empty(array_intersect($filter['product_ids'], $product_ids_in_cart))) {
    //                     return false; // Excluded products are in the cart
    //                 }
    //             }
    //         }
    //     }
    // }

    // If all conditions are passed, return true and update coupon usage
    update_coupon_usage($user_info['user_id'], $coupon['name_code']); // Update usage after coupon is applied
    return true;
}

// Function to update coupon usage in user info
function update_coupon_usage($user_id, $coupon_name) {
    // Assume user_coupon_usage is stored in a database table called "user_coupon_usage"
    // You can replace this with actual database update logic as needed

    // Fetch current coupon usage for the user
    global $wpdb; // WordPress database object
    $table_name = $wpdb->prefix . 'user_coupon_usage';

    // Check if the user has already used the coupon
    $current_usage = $wpdb->get_var($wpdb->prepare(
        "SELECT usage_count FROM $table_name WHERE user_id = %d AND coupon_name = %s",
        $user_id, $coupon_name
    ));

    if ($current_usage === null) {
        // Insert a new record if no usage is found
        $wpdb->insert(
            $table_name,
            array(
                'user_id' => $user_id,
                'coupon_name' => $coupon_name,
                'usage_count' => 1
            ),
            array('%d', '%s', '%d')
        );
    } else {
        // Update the existing record to increment the usage count
        $wpdb->update(
            $table_name,
            array('usage_count' => $current_usage + 1),
            array('user_id' => $user_id, 'coupon_name' => $coupon_name),
            array('%d'),
            array('%d', '%s')
        );
    }
}

// Example data
$coupon = [
    'status' => 'active',
    'name_code' => 'BOGO', // Coupon name
    'discount_type' => 'fixed_price',
    'discount_amt' => 20,
    'is_individual_use' => 1, // Individual use for one-time usage per user
    'coupon_limit' => 0,
    'coupon_filter' => [
        1 => [
            'based_on' => 'product_id',
            'include_exclude' => 'include',
            'product_ids' => ['55', '29'],
            'category_ids' => []
        ]
    ],
    'expiry_date' => '2024-10-25T17:42',
    'restricted_emails' => ["www.smriti19@gmail.com", "xyz2gmil.com"],
    'created_at' => '2024-10-18 17:42:37',
    'is_expired' => 0
];

$cart_items = [
    ['cart_id' => 1, 'item_id' => 27, 'item_name' => 'Beanie', 'quantity' => 2, 'price' => 18.00],
    ['cart_id' => 1, 'item_id' => 55, 'item_name' => 'Beanie with Logo', 'quantity' => 2, 'price' => 36.00],
    ['cart_id' => 1, 'item_id' => 43, 'item_name' => 'Album', 'quantity' => 1, 'price' => 15.00],
    ['cart_id' => 1, 'item_id' => 31, 'item_name' => 'Cap', 'quantity' => 2, 'price' => 32.00]
];

$user_info = [
    'user_id' => 1,
    'user_email' => 'smritis2307@gmail.com',
    'user_displayname' => 'admin',
    'user_nicename' => 'admin',
    'user_firstname' => '',
    'user_lastname' => '',
    'user_role' => ['administrator']
];

// Example of previous coupon usage by the user
$user_coupon_usage = [
    1 => ['BOGO' => 1] // User with ID 1 has used "BOGO" coupon once
];

// Check if the coupon can be used
if (can_use_coupon($coupon, $cart_items, $user_info, $user_coupon_usage)) {
    echo "Coupon can be used.";
} else {
    echo "Coupon cannot be used.";
}


//when mail send succesfully
1. updtae last_interaction_at
2. update flexi_email_logs table1
3. 