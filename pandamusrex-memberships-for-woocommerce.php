<?php
/**
 * Plugin Name: PandamusRex Memberships for WooCommerce
 * Version: 1.1.5
 * Plugin URI: https://github.com/pandamusrex/pandamusrex-memberships-for-woocommerce
 * Description: Buying this product gets you a membership!
 * Author: PandamusRex
 * Author URI: https://www.github.com/pandamusrex/
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Requires at least: 6.4
 * Requires PHP: 7.0
 * Tested up to: 6.8
 *
 * Text Domain: pandamusrex-memberships
 * Domain Path: /lang/
 *
 * @package WordPress
 * @author PandamusRex
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

require_once( plugin_dir_path(__FILE__) . 'includes/pandamusrex-memberships-user-helper.php' );
require_once( plugin_dir_path(__FILE__) . 'includes/pandamusrex-memberships-db.php' );
register_activation_hook( __FILE__, [ 'PandamusRex_Memberships_Db', 'create_tables' ] );
require_once( plugin_dir_path(__FILE__) . 'includes/pandamusrex-memberships-admin.php' );

class PandamusRex_Memberships {
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
        add_action( 'add_meta_boxes', [ $this, 'add_meta_box' ] );
        add_action( 'save_post', [ $this, 'save_postdata' ] );
        add_filter( 'manage_users_columns', [ $this, 'manage_users_columns' ] );
        add_filter( 'manage_users_custom_column',  [ $this, 'manage_users_custom_column' ], 10, 3 );
        add_filter( 'woocommerce_account_menu_items', [ $this, 'add_memberships_my_account_tab' ] );
        add_action( 'woocommerce_account_memberships-tab_endpoint', [ $this, 'memberships_my_account_tab_content' ] );
        add_action( 'init', [ $this, 'add_memberships_tab_endpoint' ] );
        add_filter( 'query_vars', [ $this, 'add_custom_query_vars' ], 0 );
        add_action( 'woocommerce_order_status_changed', [ $this, 'woocommerce_order_status_changed' ], 10, 4 );

        add_action( 'woocommerce_before_order_notes', [ $this, 'custom_checkout_fields' ] );
    }

    public function add_meta_box() {
        add_meta_box( 'pandamusrex_memberships_sectionid', __( 'Memberships', 'pandamusrex-memberships' ), array( $this, 'meta_box' ), 'product', 'side', 'high' );
    }

    public function meta_box( $product ) {
        echo '<input type="hidden" name="pandamusrex_memberships_nonce" id="pandamusrex_memberships_nonce" value="' . esc_attr( wp_create_nonce( 'pandamusrex_memberships-' . $product->ID ) ) . '" />';

        $prod_incl_membership = get_post_meta( $product->ID, '_pandamusrex_prod_incl_membership', false );

        echo '<input type="checkbox" id="_pandamusrex_prod_incl_membership" name="_pandamusrex_prod_incl_membership" ' .
            ( $prod_incl_membership ? "checked" : "" ) .
            '/>';
        echo '<label for="_pandamusrex_prod_incl_membership">' .
            esc_html__( 'Product includes membership', 'pandamusrex-memberships' ) .
            '</label>';
    }

    public function save_postdata( $product_id ) {
        if ( ! isset ( $_POST['pandamusrex_memberships_nonce'] ) )
            return $product_id;

        $nonce = sanitize_text_field( wp_unslash( $_POST['pandamusrex_memberships_nonce'] ) );
        if ( ! wp_verify_nonce( $nonce, 'pandamusrex_memberships-' . $product_id ) )
            return $product_id;

        // verify if this is an auto save routine. If it is our form has not been submitted, so we dont want
        // to do anything
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
            return $product_id;

        if (! isset( $_POST['post_type'] ) )
            return $product_id;

        // Check permissions
        if ( 'page' == $_POST['post_type'] ) {
            if ( ! current_user_can( 'edit_page', $product_id ) )
                return $product_id;
        } else {
            if ( ! current_user_can( 'edit_post', $product_id ) )
                return $product_id;
        }

        $prod_incl_membership = FALSE;
        if ( isset( $_POST['_pandamusrex_prod_incl_membership']) ) {
            $prod_incl_membership = TRUE;
        }

        if ( empty( $prod_incl_membership ) ) {
            delete_post_meta( $product_id, '_pandamusrex_prod_incl_membership' );
        } else {
            update_post_meta( $product_id, '_pandamusrex_prod_incl_membership', TRUE );
        }

        return $product_id;
    }

    public function manage_users_columns( $columns ) {
        $columns[ 'membership' ] = __( 'Membership Ended/Ends', 'pandamusrex-memberships' );
        return $columns;
    }

    public function manage_users_custom_column( $value, $column_name, $user_id ) {
        if ( 'membership' == $column_name ) {
            $most_recent = PandamusRex_Memberships_Db::getMostRecentMembershipForUser( $user_id );

            $link_text = '';
            if ( empty( $most_recent ) ) {
                $link_text = esc_html__( 'N/A', 'pandamusrex-memberships' );
            } else {
                $link_text = esc_html( $most_recent[ 'membership_ends' ] );
            }
            return $link_text;
        }

        return $value;
    }

    public function add_memberships_my_account_tab( $items ) {
        $new_array = [];
        $did_insert = false;

        foreach ( $items as $key => $value ) {
            $new_array[ $key ] = $value;
            if ( $key == 'orders' ) {
                $new_array[ 'memberships-tab' ] = __( 'Membership', 'pandamusrex-memberships' );
                $did_insert = true;
            }
        }

        // Just in case we never found Orders for some reason
        if ( ! $did_insert ) {
            $new_array[ 'memberships-tab' ] = __( 'Membership', 'pandamusrex-memberships' );
        }

        return $new_array;
    }

    public function memberships_my_account_tab_content() {
        echo '<h2>Membership</h2>';

        $user_id = get_current_user_id();
        $memberships = PandamusRex_Memberships_Db::getAllMembershipsByUser( $user_id );

        if ( empty( $memberships ) ) {
            echo '<p>';
            echo esc_html__( 'You have no membership... yet!', 'pandamusrex-memberships' );
            echo '</p>';
        } else {
            echo '<table class="shop_table">';
            echo '<tr>';
            echo '<th>';
            echo esc_html__( 'Order #', 'pandamusrex-memberships' );
            echo '</th>';
            echo '<th>';
            echo esc_html__( 'Membership Started', 'pandamusrex-memberships' );
            echo '</th>';
            echo '<th>';
            echo esc_html__( 'Membership Ended/Ends', 'pandamusrex-memberships' );
            echo '</th>';
            echo '</tr>';

            foreach( $memberships as $membership ) {
                echo '<tr>';
                echo '<td>';
                echo $membership[ 'order_id' ];
                echo '</td>';
                echo '<td>';
                echo $membership[ 'membership_starts' ];
                echo '</td>';
                echo '<td>';
                echo $membership[ 'membership_ends' ];
                echo '</td>';
                echo '</td>';
                echo '</tr>';
            }

            echo '</table>';
        }
    }

    public function add_memberships_tab_endpoint() {
        add_rewrite_endpoint( 'memberships-tab', EP_ROOT | EP_PAGES );
    }

    public function add_custom_query_vars( $vars ) {
        $vars[] = 'memberships-tab';
        return $vars;
    }

    public function woocommerce_order_status_changed( $order_id, $old_status, $new_status, $order ) {
        // wc_get_logger()->debug( "in woocommerce_order_status_changed, new_status = $new_status" );

        if ( $new_status != "completed" ) {
            return;
        }

        // wc_get_logger()->debug( "Order ID: $order_id" );

        $buyer_user_id = $order->get_user_id();
        if ( $buyer_user_id == 0 ) {
            if ( function_exists( 'wc_get_logger' ) ) {
                wc_get_logger()->debug( "Unexpectedly got guest buyer in payment complete for order $order_id" );
            } else {
                error_log( "Unexpectedly got guest buyer in payment complete for order $order_id" );
            }
            return;
        }

        // wc_get_logger()->debug( "User ID: $user_id" );

        $found_product_id = 0;

        foreach ( $order->get_items() as $item ) {
            $product_id = $item->get_product_id();
            // wc_get_logger()->debug( "Examining product $product_id" );

            $prod_incl_membership = get_post_meta( $product_id, '_pandamusrex_prod_incl_membership', false );
            if ( $prod_incl_membership ) {
                $quantity = $item->get_quantity();

                // "First" item always goes to buyer
                // Second or higher item (if present) goes to emails provided during checkout
                // _pandamus_members_{product_id}_recipient_email_{2..3..4}

                // First, the buyer
                $result = PandamusRex_Memberships_Db::addMembershipForUserThatStartsNow(
                    $buyer_user_id,
                    $product_id,
                    $order_id,
                    'Created for buyer automatically on payment complete'
                );

                if ( is_wp_error( $result ) ) {
                    wc_get_logger()->debug( "Unable to add membership for user_id $buyer_user_id for order_id $order_id and product_id $product_id" );
                }

                // Now, anyone else
                // e.g. if quantity = 3
                // look in order meta for
                // _pandamus_members_{product_id}_recipient_email_2
                // _pandamus_members_{product_id}_recipient_email_3
                for ( $index = 2; $index <= $quantity; $index++ ) {
                    $meta_key = '_pandamus_members_' . $product_id . '_recipient_email_' . $index;
                    // Find or create the user based on the order meta
                    $user_id = -1;
                    $recipient_email = get_post_meta( $order_id, $meta_key, true ); // true: single
                    if ( is_email( $recipient_email ) ) {
                        $user_id = PandamusRex_Memberships_User_Helper::find_or_create_user( $recipient_email );
                    } else {
                        wc_get_logger()->debug( "Unable to create user for recipient $index for product_id $product_id for order_id $order_id - invalid email $recipient_email" );
                        continue; // don't attempt to add membership - it can be created manually later
                    }

                    $result = PandamusRex_Memberships_Db::addMembershipForUserThatStartsNow(
                        $user_id,
                        $product_id,
                        $order_id,
                        'Created for recipient automatically on payment complete'
                    );

                    if ( is_wp_error( $result ) ) {
                        wc_get_logger()->debug( "Unable to add membership for user_id $user_id for order_id $order_id and product_id $product_id" );
                    }
                }
            }
        }
    }

    public function custom_checkout_fields( $checkout ) {
        $cart = WC()->cart->get_cart();

        // First, see if we have any membership products with quantity > 1
        // before we output anything
        $show_div = false;
        foreach ( $cart as $cart_item_key => $cart_item ) {
            $product = apply_filters(
                'woocommerce_cart_item_product',
                $cart_item['data'],
                $cart_item,
                $cart_item_key
            );
            if ( $product && $product->exists() && $cart_item['quantity'] > 1 ) {
                $show_div = true;
            }
        }

        if ( ! $show_div ) {
            return;
        }

        echo '<div id="pandamusrex_memberships_recipients_fields">';
        echo '<h3>';
        echo esc_html__(
            'You have multiple memberships in your cart. Please provide an email address for each recipient besides yourself',
            'pandamusrex-memberships');
        echo '</h3>';

        foreach ( $cart as $cart_item_key => $cart_item ) {
            $product = apply_filters(
                'woocommerce_cart_item_product',
                $cart_item['data'],
                $cart_item,
                $cart_item_key
            );
            if ( $product && $product->exists() && $cart_item['quantity'] > 1 ) {
                for ( $index = 2; $index <= $cart_item['quantity']; $index++ ) {
                    $product_id = $product->get_id();
                    $product_name = $product->get_name();

                    // pandamus_members_{product_id}_recipient_email_2
                    $custom_field_name = "pandamus_members_{$product_id}_recipient_email_$index";
                    $label = $product_name . " - Membership $index Recipient Email";
                    woocommerce_form_field( $custom_field_name, array(
                        'type'        => 'email',
                        'required'    => true,
                        'class'       => array('pandamusrex_membership_checkout_recipient form-row-wide'),
                        'label'       => $label,
                        'placeholder' => __( '' ),
                    ), $checkout->get_value( $custom_field_name ) );
                }
            }
        }

        echo '</div>';
    }
}

PandamusRex_Memberships::get_instance();
