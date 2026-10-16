<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class PandamusRex_Memberships_Products_Admin {
    private static $instance;

    public static function get_instance() {
        if ( null == self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __clone() {}

    public function __wakeup() {}

    public function __construct() {
        add_action( 'admin_menu', [ $this, 'admin_menu' ] );
    }

    public function admin_menu() {
        add_submenu_page(
            'pandamusrex_memberships_page',
            __( 'Membership Products', 'pandamusrex-memberships' ),
            __( 'Membership Products', 'pandamusrex-memberships' ),
            'manage_options',
            'pandamusrex_membership_products_page',
            [ $this, 'pandamusrex_membership_products_page' ]
        );
    }

    public function pandamusrex_membership_products_page() {
        if ( ! isset( $_GET['product_id'] ) ) {
            $this->show_product_list();
        } else {
            $this->show_single_product_details();
        }
    }

    public function show_product_list() {
        echo '<div class="wrap">';
        echo '<h1 class="wp-heading-inline">';
        esc_html_e( 'Membership Products', 'pandamusrex-memberships' );
        echo '<p>';
        esc_html_e( 'Lists all the products that include membership', 'pandamusrex-memberships' );
        echo '</p>';
        echo '</h1>';
        echo '<hr class="wp-header-end">';
        echo '<table class="wp-list-table widefat fixed striped table-view-list">';
        echo '<tr>';
        echo '<th>';
        echo esc_html__( 'Product ID', 'pandamusrex-memberships' );
        echo '</th>';
        echo '<th>';
        echo esc_html__( 'Membership Product', 'pandamusrex-memberships' );
        echo '</th>';
        echo '</tr>';
        
        $product_ids = $this->get_all_membership_product_ids();

        if (empty($product_ids)) {
            echo '<tr><td>';
            esc_html_e( 'No products found with Product Includes Membership checked', 'pandamusrex-memberships' );
            echo '</td></tr>';
        } else {
            foreach ($product_ids as $product_id) {

                $url = admin_url("admin.php?page=pandamusrex_membership_products_page&product_id=$product_id");
                $product_name = "Unknown membership product";
                $product = wc_get_product( $product_id );
                if ( $product ) {
                    $product_name = $product->get_name();
                }

                echo '<tr>';
                echo '<td>';
                echo "<a href='" . esc_url($url) . "'>" . esc_attr($product_id) . "</a>";
                echo '</td>';
                echo '<td>';
                echo "<a href='" . esc_url($url) . "'>" . esc_attr($product_name) . "</a>";
                echo '</td>';
                echo '</tr>';
            }
        }

        echo '</table>';

        echo '</div>'; //wrap
    }

    public function get_all_membership_product_ids() {
        // Gets the IDs of all simple AND variable products that have "Product Includes Membership" checked
        $product_ids = [];

        $product_ids = wc_get_products( array(
            'limit'      => -1,          // Get all matching products
            'status'     => 'publish',   // Only published products
            'meta_key'   => '_pandamusrex_prod_incl_membership',
            'meta_value' => true,
            'order'      => 'DESC',
            'orderby'    => 'ID',
            'return'     => 'ids'        // Return just the product IDs
        ));

        return $product_ids;
    }

    public function get_orders_by_product_or_variations( $product_id ) {
        $order_ids = [];

        // First, get all completed orders
        // (Unfortunately you cannot query on product_ids)

        $order_ids = wc_get_orders(
            [
                'limit'  => -1,
                'return' => 'ids',
                'status' => 'completed'
            ]
        );

        // Look in each order for our product_id or its variations
        $order_buyer_map = [];

        foreach ($order_ids as $order_id) {
            $order = wc_get_order($order_id);
            $buyer_last_name = $order->get_billing_last_name();
            foreach ( $order->get_items() as $item_id => $item ) {
                $found_product_id = $item->get_product_id();
                $found_variation_id = $item->get_variation_id();

                if ($found_product_id == $product_id) {
                    $order_buyer_map[$order_id] = $buyer_last_name;
                }
            }
        }

        // sort the order buyer map by buyer last name
        asort($order_buyer_map);

        // return just the order ids (the keys) from the sorted map
        return array_keys($order_buyer_map);
    }

    public function show_single_product_details() {
        if ( ! isset( $_GET['product_id'] ) ) {
            wp_admin_notice(
                __( 'Missing product ID', 'pandamusrex-memberships' ),
                [ 'type' => 'error' ]
            );
            return;
        }

        $product_id = intval($_GET['product_id']);
            $product = wc_get_product( $product_id );
            if (! $product) {
                wp_admin_notice(
                __( 'Invalid product ID', 'pandamusrex-memberships' ),
                [ 'type' => 'error' ]
            );
            return;
        }

        $has_membership = get_post_meta($product_id, '_pandamusrex_prod_incl_membership', true);
        if (empty($has_membership)) {
            wp_admin_notice(
                __( 'Product does not include membership', 'pandamusrex-memberships' ),
                [ 'type' => 'error' ]
            );
        }

        $product_title = $product->get_title();
        echo '<div class="wrap">';
        echo '<h1 class="wp-heading-inline">';
        esc_html_e( 'Membership Product', 'pandamusrex-memberships' );
        echo '</h1>';
        echo '<p>';
        esc_html_e( 'Lists all the orders for a single membership product and allows you to check people into events', 'pandamusrex-memberships' );
        echo '</p>';
        echo '<h2>';
        esc_html_e('Product: ', 'pandamusrex-memberships');
        echo esc_attr($product_title);
        echo '</h2>';
        echo '<hr class="wp-header-end">';

        echo '<hr class="wp-header-end">';
        echo '<table class="wp-list-table widefat fixed striped table-view-list">';
        echo '<tr>';
        echo '<th>';
        echo esc_html__( 'Buyer (Last Name, First Name)', 'pandamusrex-memberships' );
        echo '</th>';
        echo '<th>';
        echo esc_html__( 'Order ID', 'pandamusrex-memberships' );
        echo '</th>';
        echo '<th>';
        echo esc_html__( 'Recipient(s) and Product/Variation(s)', 'pandamusrex-memberships' );
        echo '</th>';
        echo '<th>';
        echo esc_html__( 'Checked-In?', 'pandamusrex-memberships' );
        echo '</th>';
        echo '</tr>';

        if ( ! isset( $_GET['product_id'] ) ) {
            echo "<tr>";
            echo "<td>";
            echo esc_html__( 'Error: Expected product_id in URL', 'pandamusrex-memberships' );
            echo "</td>";
            echo "</tr>";
        } else {
            $product_id = intval($_GET['product_id']);
            $product = wc_get_product( $product_id );
            if (! $product) {
                echo "<tr>";
                echo "<td>";
                echo esc_html__( 'Error: Could not find product', 'pandamusrex-memberships' );
                echo "</td>";
                echo "</tr>";
            } else {
                $matching_order_ids = $this->get_orders_by_product_or_variations( $product_id );
                if (empty($matching_order_ids)) {
                    echo "<tr>";
                    echo "<td>";
                    echo esc_html__( 'No completed orders found for product', 'pandamusrex-memberships' );
                    echo "</td>";
                    echo "</tr>";
                } else {
                    foreach ($matching_order_ids as $order_id) {
                        $this->emit_rows_for_order($order_id);
                    }
                }
            }
        }

        echo '</table>';

        echo '</div>'; //wrap
    }

    public function emit_rows_for_order($order_id) {
        // Put each recipient on a separate line in the table to make it
        // easier to see them

        $order = wc_get_order($order_id);
        $order_url = get_edit_post_link($order_id);
        $buyer_email = $order->get_billing_email();
        $buyer_first_name = $order->get_billing_first_name();
        $buyer_last_name = $order->get_billing_last_name();

        foreach ( $order->get_items() as $item_id => $item ) {
            $product = $item->get_product();
            $parent_id = $product->get_parent_id();
            if ( $parent_id > 0 ) {
                // It's a variation product
                $product_id = $item->get_variation_id();
            } else {
                // It's a simple product
                $product_id = $product->get_id();
            }
            $product_name = $item->get_name();
            $quantity = $item->get_quantity();

            for ($num = 1; $num <= $quantity; $num++) {
                echo "<tr>";
                echo "<td>";
                $this->emit_billing_name_for_order($buyer_first_name, $buyer_last_name);
                echo "</td>";
                echo "<td>";
                echo "<a href='" . esc_url($order_url) . "'>" . $order_id . "</a>";
                echo "</td>";

                $meta_key = 'pandamusrex_memberships_product_' . $product_id . '_recipient_' . $num;
                $recipient_email = $order->get_meta($meta_key);
    
                echo "<td>";
                echo "Recipient: ";
                echo "<b>";
                $this->emit_personal_info_for_email($recipient_email);
                echo "</b>";
                echo "<br/>";
                echo "<br/>";
                echo "Product/Variation: " . $product_name;
                echo "</td>";

                $slugified_email = $this->slugify_email($recipient_email);
                $check_in_meta_key = 'panmem_check_in_' . $slugified_email;
                if ($parent_id > 0 ) {
                    // Look for the check in meta on the PARENT for variation products
                    // Why? It doesn't make sense to check in for dietary restriction/dessert variants
                    // It DOES make sense to check into the event that has those as attributes
                    $parent_product = wc_get_product($parent_id);
                    $is_checked_in = ! empty($parent_product->get_meta($check_in_meta_key));
                    $check_in_product_id = $parent_id;
                } else {
                    // Look for the check in meta on the ACTUAL product for simple products
                    $is_checked_in = ! empty($product->get_meta($check_in_meta_key));
                    $check_in_product_id = $product_id;
                }

                echo "<td>";
                if ($is_checked_in) {
                    echo "<button class='pandamusrex_memberships_check_in' data-product-id='" . $check_in_product_id . "' value='" . $check_in_meta_key . "' name='kick_them_out'>";
                    echo "&#9989; ";
                    echo esc_html__( 'Checked In', 'pandamusrex-memberships' );
                    echo "</button>";
                } else {
                    echo "<button class='pandamusrex_memberships_check_in' data-product-id='" . $check_in_product_id . "' value='" . $check_in_meta_key . "' name='check_them_in'>";
                    echo "&#10060; ";
                    echo esc_html__( 'Not Checked In', 'pandamusrex-memberships' );
                    echo "</button>";
                }
                echo "</td>";
                echo "</tr>";
            }
        }
    }

    public function emit_billing_name_for_order($first_name, $last_name) {
        if (! empty($first_name)) {
            if (! empty($last_name)) { // We have a first and a last name for the buyer
                echo "<b>";
                echo esc_attr($last_name);
                echo ", ";
                echo esc_attr($first_name);
                echo "</b>";
                echo "<br/>";
                echo "<br/>";
            } else { // We have just a first name for the buyer
                echo "<b>";
                echo esc_attr($first_name);
                echo "</b>";
                echo "<br/>";
                echo "<br/>";
            }
        } else {
            if (! empty($last_name)) { // We have just a last name for the buyer
                echo "<b>";
                echo esc_attr($last_name);
                echo "</b>";
                echo "<br/>";
                echo "<br/>";
            }
        }
    }

    public function emit_personal_info_for_email($email) {
        // First "Furry" Last email
        $user_obj = get_user_by('email', $email);
        if (! $user_obj) {
            echo esc_attr($email);
            return;
        }

        $first_name = $user_obj->first_name;
        $last_name = $user_obj->last_name;
        $nickname = get_user_meta( $user_obj->ID, 'nickname', true );

        if (! empty($first_name)) {
            echo esc_attr($first_name);
            echo " ";
        }

        if (! empty($nickname)) {
            echo '"';
            echo esc_attr($nickname);
            echo '"';
            echo " ";
        }

        if (! empty($last_name)) {
            echo esc_attr($last_name);
            echo " ";
        }

        echo esc_attr($email);
    }

    public function slugify_email($email) {
        // replace special characters in an email address with _
        // so we can use it as a meta key for check-ins
        $slug = str_replace(['.', '-', '+', '@'], '_', $email);
        return $slug;
    }
}

PandamusRex_Memberships_Products_Admin::get_instance();